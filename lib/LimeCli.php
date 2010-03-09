<?php

/*
 * This file is part of the Lime framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Bernhard Schussek <bernhard.schussek@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Runs the Lime CLI commands.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeCli
{
  protected static $allowedOptions = array(
    'help',
    'init',
    'processes',
    'suffix',
    'color',
    'verbose',
    'serialize',
    'output',
    'test',
  );

  /**
   * Runs a command with the given CLI arguments.
   *
   * @param  array $arguments  The CLI arguments
   * @return integer           The return value of the command (0 if successful)
   */
  public function run(array $arguments)
  {
    try
    {
      list($options, $labels) = $this->parseArguments($arguments);

      if ($diff = array_diff(array_keys($options), self::$allowedOptions))
      {
        throw new Exception(sprintf('Unknown option(s): "%s"', implode('", "', $diff)));
      }

      if (isset($options['help']))
      {
        return $this->usage($options);
      }
      else if (isset($options['init']))
      {
        return $this->init($options);
      }
      else
      {
        return $this->test(array_slice($labels, 1), $options);
      }
    }
    catch (Exception $e)
    {
      echo $e->getMessage()."\n";

      return 1;
    }
  }

  /**
   * Prints the usage information.
   *
   * @return integer  The return value of the command (0 if successful)
   */
  protected function usage(array $options)
  {
    echo <<<EOF
Command line utility for the Lime 2 test framework.

Usage:
  Execute all tests set up in lime.config.php:

    lime

  Execute the test with a specific name:

    lime --test=<name>

  The name is the test file name without the suffix configured in
  lime.config.php.

  Execute all tests in <label1> AND <label2>:

    lime <label1> <label2>...

  Execute all tests in <label1> OR <label2>:

    lime <label1> +<label2>...

  Execute all tests in <label1> EXCEPT those also in <label2>:

    lime <label1> -<label2>...


Options:
  --color                 Enforces colorization in the console output.
  --help                  This help
  --init                  Initializes the current working directory for
                          use with Lime 2. You should adapt the generated
                          lime.config.php to include your test files and
                          to set up labels.
  --output=<output>       Changes the output of the test. Can be one of
                          "raw", "xml", "suite" and "tap".
  --processes=<n>         Sets the number of processes to use.
  --serialize             Enables serialization of the output. Only works
                          with some output types (option --output).
  --test=<test>           Executes a single test. The test name is the file name
                          without the suffix configured in lime.config.php.
  --verbose               Enables verbose output. Only works with some
                          output types (option --output).

Examples:
  Execute MyClassTest:

    lime --test=MyClass

  Execute all tests that are in label "unit" and "model" at the same
  time, but that are not in label "slow":

    lime unit model -slow

  Execute all tests in label "unit" and all tests in label
  "functional":

    lime unit +functional

Configuration:
  The configuration file named lime.config.php is first searched in the
  current directory, then recursively in all parent directories. This
  means that you can launch lime also from subdirectories of your project.

  Included test files and test labels can be configured in the
  configuration file. See the user documentation for more information.


EOF;

    return 0;
  }

  /**
   * Initializes a project for use with Lime.
   *
   * @return integer  The return value of the command (0 if successful)
   */
  protected function init(array $options)
  {
    $absoluteLimeDir = realpath(dirname(__FILE__).'/..');
    $skeletonDir = $absoluteLimeDir.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'skeleton';
    $projectDir = realpath(getcwd());

    if (strpos($absoluteLimeDir, $projectDir.DIRECTORY_SEPARATOR) === 0)
    {
      $relativeLimeDir = substr($absoluteLimeDir, strlen($projectDir.DIRECTORY_SEPARATOR));
    }

    echo "Creating lime.config.php...";

    if (!file_exists($path = $projectDir.DIRECTORY_SEPARATOR.LimeConfiguration::FILENAME))
    {
      $content = file_get_contents($skeletonDir.DIRECTORY_SEPARATOR.LimeConfiguration::FILENAME);

      file_put_contents($path, str_replace("\n", PHP_EOL, $content));

    }
    else
    {
      echo " exists already!";
    }

    echo "\nCreating lime executable...";

    if (!file_exists($path = $projectDir.DIRECTORY_SEPARATOR.'lime'))
    {
      ob_start();
      include $skeletonDir.DIRECTORY_SEPARATOR.'lime';
      $content = ob_get_clean();

      file_put_contents($path, str_replace(array('[?php', "\n"), array('<?php', PHP_EOL), $content));
      chmod($path, 0777);
    }
    else
    {
      echo " exists already!";
    }

    echo <<<EOF

Initialized Lime project in $projectDir.

Please add your test files to lime.config.php.
You can find out more about Lime by running

    php lime --help


EOF;

    return 0;
  }

  /**
   * Tests a given set of labels.
   *
   * Packages may given with a leading "+" or "-". The tested files are:
   *
   *    * all files that are in all of the labels without leading "+" or "-"
   *    * all files that are in any label with a leading "+"
   *    * no files that are in any label with a leading "-"
   *
   * @param  array $labels  The label names
   * @return integer        The return value of the command (0 if successful)
   */
  protected function test(array $labels, array $options)
  {
    $configuration = LimeConfiguration::getInstance(getcwd());

    if ($configuration->getLegacyMode())
    {
      LimeAutoloader::enableLegacyMode();
    }

    if (isset($options['processes']))
    {
      $configuration->setProcesses($options['processes']);
    }

    if (isset($options['suffix']))
    {
      $configuration->setSuffix($options['suffix']);
    }

    if (isset($options['output']))
    {
      $configuration->setTestOutput($options['output']);
      $configuration->setSuiteOutput($options['output']);
    }

    if (isset($options['color']))
    {
      $configuration->setForceColors(true);
    }

    if (isset($options['verbose']))
    {
      $configuration->setVerbose(true);
    }

    if (isset($options['serialize']))
    {
      $configuration->setSerialize(true);
    }

    if (isset($options['test']))
    {
      $fileName = $options['test'];

      if (!is_readable($fileName))
      {
        $loader = new LimeLoader($configuration);
        $files = $loader->getFilesByName($options['test']);

        if (count($files) == 0)
        {
          throw new Exception("No tests are registered in the test suite! Please add your tests in lime.config.php.");
        }
        else if (count($files) > 1)
        {
          $paths = array();
          foreach ($files as $file)
          {
            $paths[] = $file->getPath();
          }

          throw new Exception(sprintf("The name \"%s\" is ambiguous:\n  - %s\nPlease launch the test with the full file path.", $labels[0], implode("\n  - ", $paths)));
        }

        $fileName = $files[0]->getPath();
      }

      if ($configuration->getAnnotationSupport())
      {
        $support = new LimeAnnotationSupport($fileName);

        return $support->execute();
      }
      else
      {
        return $this->includeTest($fileName);
      }
    }
    else
    {
      $loader = new LimeLoader($configuration);
      $harness = new LimeHarness($configuration, $loader);
      $files = $loader->getFilesByLabels($labels);

      if (count($files) == 0)
      {
        throw new Exception("No tests are registered in the test suite! Please add your tests in lime.config.php.");
      }

      return $harness->run($files) ? 0 : 1;
    }
  }

  protected function includeTest($__lime_path)
  {
  	LimeAnnotationSupport::setScriptPath($__lime_path);

  	$lexer = new LimeLexerVariables(array('Test', 'Before', 'After', 'BeforeAll', 'AfterAll'), array('Before'));

  	// make global variables _really_ global (in case someone uses "global" statements)
  	foreach ($lexer->parse(file_get_contents($__lime_path)) as $__lime_variable)
  	{
  	  $__lime_variable = substr($__lime_variable, 1); // strip '$'
  	  global $$__lime_variable;
  	}

  	return include $__lime_path;
  }

  /**
   * Parses the given CLI arguments and returns an array of options.
   *
   * @param  array $arguments
   * @return array
   */
  protected function parseArguments(array $arguments)
  {
    $options = array();
    $parameters = array();

    foreach ($arguments as $argument)
    {
      if (preg_match('/^--([a-zA-Z\-]+)=(.+)$/', $argument, $matches))
      {
        if (in_array($matches[2], array('true', 'false')))
        {
          $matches[2] = eval($matches[2]);
        }

        $options[$matches[1]] = $matches[2];
      }
      else if (preg_match('/^--([a-zA-Z\-]+)$/', $argument, $matches))
      {
        $options[$matches[1]] = true;
      }
      else
      {
        $parameters[] = $argument;
      }
    }

    return array($options, $parameters);
  }
}