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
 * An executable used to execute test files.
 *
 * Executables know about the programm that is used to execute the test files,
 * the parser that is used to read their output and optionally some arguments
 * that are passed to the test file execution.
 *
 * This class offers two factory method for creating new executables:
 *
 * LimeExecutable::php() automatically prepends the call with the local PHP
 * binary. You can pass the name of the PHP script to which you want to pass the
 * test file in the first parameter. If you want to execute the script directly
 * with PHP, you can leave the first parameter at its default null.
 *
 * LimeExecutable::shell() creates an executable that executes a test file
 * in the console without PHP. If you pass the name of a program to the first
 * parameter, this program will be launched with the test file as first
 * argument.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeExecutable
{
  protected static
    $php             = null;

  protected
    $executable      = null,
    $arguments       = array(),
    $parserName      = null;

  /**
   * Creates a new executable executed with the PHP binary.
   *
   * The PHP binary is launched to execute the given executable with the test
   * file as first argument. If the executable is omitted or set to NULL, the
   * test file is launched directly with PHP.
   *
   * @param  string $executable  The name of the executable PHP script
   * @param  string $parserName  The parser used to parse the test file output
   * @param  array $arguments    The default arguments passed to the script
   * @return LimeExecutable
   */
  public static function php($executable = null, $parserName = null, array $arguments = array())
  {
    return new LimeExecutable(trim(self::findPhp().' '.$executable), $parserName, $arguments);
  }

  /**
   * Creates a new executable.
   *
   * The given executable is launched with the test file as first argument. If
   * the executable is omitted or set to NULL, the test file is launched
   * directly.
   *
   * @param  string $executable  The name of the executable
   * @param  string $parserName  The parser used to parse the test file output
   * @param  array $arguments    The default arguments passed to the executable
   * @return LimeExecutable
   */
  public static function shell($executable = null, $parserName = null, array $arguments = array())
  {
    return new LimeExecutable($executable, $parserName, $arguments);
  }

  /**
   * Private constructor.
   *
   * This constructor is private. You should use the factory methods php() or
   * shell() instead.
   *
   * @param string $executable
   * @param string $parserName
   * @param array $arguments
   */
  private function __construct($executable = null, $parserName = null, array $arguments = array())
  {
    $this->executable = $executable;
    $this->arguments = $arguments;
    $this->parserName = $parserName;
  }

  /**
   * Returns the name of the executable.
   *
   * @return string
   */
  public function getExecutable()
  {
    return $this->executable;
  }

  /**
   * Returns the default arguments for the executable.
   *
   * @return array
   */
  public function getArguments()
  {
    return $this->arguments;
  }

  /**
   * Returns the name of the parser used to read test file output.
   *
   * This name should be known to the used parser factory configured in
   * LimeConfiguration (LimeParserFactory by default).
   *
   * @return string
   */
  public function getParserName()
  {
    return $this->parserName;
  }

  /**
   * Tries to find the system's PHP executable and returns it.
   *
   * @return string
   */
  protected static function findPhp()
  {
    if (is_null(self::$php))
    {
      if (getenv('PHP_PATH'))
      {
        self::$executable = getenv('PHP_PATH');

        if (!is_executable(self::$php))
        {
          throw new Exception('The defined PHP_PATH environment variable is not a valid PHP executable.');
        }
      }
      else
      {
        self::$php = PHP_BINDIR.DIRECTORY_SEPARATOR.'php';
      }
    }

    if (!is_executable(self::$php))
    {
      $path = getenv('PATH') ? getenv('PATH') : getenv('Path');
      $extensions = DIRECTORY_SEPARATOR == '\\' ? (getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : array('.exe', '.bat', '.cmd', '.com')) : array('');
      foreach (array('php5', 'php') as $executable)
      {
        foreach ($extensions as $extension)
        {
          foreach (explode(PATH_SEPARATOR, $path) as $dir)
          {
            $file = $dir.DIRECTORY_SEPARATOR.$executable.$extension;
            if (is_executable($file))
            {
              self::$php = $file;
              break 3;
            }
          }
        }
      }

      if (!is_executable(self::$php))
      {
        throw new Exception("Unable to find PHP executable.");
      }
    }

    return self::$php;
  }
}