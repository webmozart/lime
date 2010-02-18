<?php

/**
 * Configuration class for Lime.
 *
 * You can obtain the configuration by calling getInstance() with the
 * path of the directory where you want to search for the configuration file.
 * The configuration file is expected to be named self::FILENAME and is
 * first searched in the given directory, then recursively in all parent
 * directories until the file is found. If no file is found, an exception
 * is thrown.
 *
 * In the configuration file, you have access to the variable $config, which
 * points to the instance of the LimeConfiguration class. You can there
 * modify the configuration.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeConfiguration
{
  const
    FILENAME       = 'lime.config.php';

  private static
    $instances     = array();

  private
    $loadables       = array(),
    $files           = array(),
    $dirs            = array(),
    $globs           = array(),
    $callbacks       = array(),
    $baseDir         = null,
    $suffix          = 'Test.php',
    $pattern         = '/Test\.php$/',
    $loader          = null,
    $processes       = 1,
    $outputFactory   = null,
    $suiteOutput     = null,
    $testOutput      = null,
    $serialize       = false,
    $verbose         = false,
    $forceColors     = false;

  /**
   * Searches for the configuration file named self::FILENAME in the given
   * directory.
   *
   * If no file is found, the file is searched recursively in the parent
   * directories.
   *
   * @param  string $directory  The path to the directory where to search
   * @return LimeConfiguration  The configuration
   * @throws Exception          If no configuration file is found
   */
  public static function getInstance($directory)
  {
    if ($directory == dirname($directory)) // root
    {
      throw new Exception(sprintf('Could not find the configuration file "%s". Run "lime --help" for usage information.', self::FILENAME));
    }

    if (isset(self::$instances[$directory]))
    {
      return self::$instances[$directory];
    }

    if (is_readable($path = $directory.DIRECTORY_SEPARATOR.self::FILENAME))
    {
      $config = new LimeConfiguration();

      include $path;

      self::$instances[$directory] = $config;

      return $config;
    }
    else
    {
      $config = self::getInstance(dirname($directory));

      self::$instances[$directory] = $config;

      return $config;
    }
  }

  /**
   * This class may not instantiated by other classes.
   */
  private function __construct()
  {
  }

  /**
   * Sets the number of processes to use for testing.
   *
   * @param integer $processes
   */
  public function setProcesses($processes)
  {
    $this->processes = $processes;
  }

  /**
   * Returns the number of processes to use for testing.
   *
   * @return integer
   */
  public function getProcesses()
  {
    return $this->processes;
  }

  /**
   * Sets the suffix of test files.
   *
   * @param string $suffix
   */
  public function setSuffix($suffix)
  {
    $this->suffix = $suffix;
    $this->pattern = '/'.preg_quote($this->suffix).'$/';
  }

  /**
   * Returns the suffix of test files.
   *
   * @return string
   */
  public function getSuffix()
  {
    return $this->suffix;
  }

  /**
   * Sets the base directory for the registered files.
   *
   * @param string $baseDir
   */
  public function setBaseDir($baseDir)
  {
    $this->baseDir = $baseDir;
  }

  /**
   * Returns the base directory for registered files.
   *
   * @return string
   */
  public function getBaseDir()
  {
    return $this->baseDir;
  }

  /**
   * Sets the factory used for creating output instances.
   *
   * @param LimeOutputFactoryInterface $factory
   */
  public function setOutputFactory(LimeOutputFactoryInterface $factory)
  {
    $this->outputFactory = $factory;
  }

  /**
   * Returns the factory used for creating output instances.
   *
   * @return LimeOutputfactoryInterface
   */
  public function getOutputFactory()
  {
    return $this->outputFactory;
  }

  /**
   * Sets the factory used for creating parser instances.
   *
   * @param LimeParserFactoryInterface $factory
   */
  public function setParserFactory(LimeParserFactoryInterface $factory)
  {
    $this->parserFactory = $factory;
  }

  /**
   * Returns the factory used for creating parser instances.
   *
   * @return LimeParserFactoryInterface
   */
  public function getParserFactory()
  {
    return $this->parserFactory;
  }

  /**
   * Sets the name of the output used for test suites.
   *
   * @param string $output
   */
  public function setSuiteOutput($output)
  {
    $this->suiteOutput = $output;
  }

  /**
   * Sets the name of the output used for single tests.
   *
   * @param string $output
   */
  public function setTestOutput($output)
  {
    $this->testOutput = $output;
  }

  /**
   * Creates a new output instance for a test suite.
   *
   * @return LimeOutputInterface
   */
  public function createSuiteOutput()
  {
    if (is_null($this->outputFactory))
    {
      throw new LogicException('You must register an output factory before creating outputs');
    }

    if (is_null($this->suiteOutput))
    {
      throw new LogicException('You must set the output name before creating the output');
    }

    return $this->outputFactory->create($this->suiteOutput);
  }

  /**
   * Creates a new output instance for a single test.
   *
   * @return LimeOutputInterface
   */
  public function createTestOutput()
  {
    if (is_null($this->outputFactory))
    {
      throw new LogicException('You must register an output factory before creating outputs');
    }

    if (is_null($this->testOutput))
    {
      throw new LogicException('You must set the output name before creating the output');
    }

    return $this->outputFactory->create($this->testOutput);
  }

  /**
   * Sets whether the output should be serialized.
   *
   * @param boolean $serialize
   */
  public function setSerialize($serialize)
  {
    $this->serialize = $serialize;
  }

  /**
   * Returns whether the output should be serialized.
   *
   * @return boolean
   */
  public function getSerialize()
  {
    return $this->serialize;
  }

  /**
   * Sets whether the output should be verbose.
   *
   * @param boolean $verbose
   */
  public function setVerbose($verbose)
  {
    $this->verbose = $verbose;
  }

  /**
   * Returns whether the output should be verbose.
   *
   * @return boolean
   */
  public function getVerbose()
  {
    return $this->verbose;
  }

  /**
   * Sets whether to enforce colorization in the output.
   *
   * @param boolean $force
   */
  public function setForceColors($force)
  {
    $this->forceColors = $force;
  }

  /**
   * Returns whether to enforce colorization in the output.
   *
   * @return boolean
   */
  public function getForceColors()
  {
    return $this->forceColors;
  }

  /**
   * Registers a test file path in the test suite.
   *
   * @param string $path
   * @param array $labels
   */
  public function registerFile($path, LimeExecutable $executable, $labels = array())
  {
    $this->loadables[] = new LimeFile($this->getAbsolutePath($path), $executable, $labels);
  }

  /**
   * Marks a directory to be added to the test suite with doRegisterDir().
   *
   * @param string $path
   * @param array $labels
   */
  public function registerDir($path, LimeExecutable $executable, $labels = array())
  {
    $this->loadables[] = new LimeDirectory($this->getAbsolutePath($path), $this->pattern, $executable, $labels);
  }

  /**
   * Marks a glob to be added to the test suite with doRegisterGlob().
   *
   * @param string $glob
   * @param array $labels
   */
  public function registerGlob($glob, LimeExecutable $executable, $labels = array())
  {
    $this->loadables[] = new LimeGlob($this->getAbsolutePath($glob), $executable, $labels);
  }

  /**
   * Adds the results of a callback to be added to the test suite.
   *
   * The callback should return an array of file paths.
   *
   * @param callable $callback
   * @param array $labels
   */
  public function registerCallback($callback, LimeExecutable $executable, $labels = array())
  {
    $this->loadables[] = new LimeCallback($callback, $executable, $labels);
  }

  /**
   * Returns all registered loadables.
   *
   * @return array
   */
  public function getLoadables()
  {
    return $this->loadables;
  }

  /**
   * Returns the absolute version of the given path.
   *
   * If the path is not already absolute, the base directory of the
   * configuration is prepended in front of the path.
   *
   * @param  string $path
   * @return string
   */
  protected function getAbsolutePath($path)
  {
    if (realpath($path) != $path)
    {
      return $this->getBaseDir().DIRECTORY_SEPARATOR.$path;
    }
    else
    {
      return $path;
    }
  }
}