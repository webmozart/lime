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
 * An command used to execute test files.
 *
 * Executables know about the programm that is used to execute the test files,
 * the input that is used to read their output and optionally some arguments
 * that are passed to the test file execution.
 *
 * This class offers two factory method for creating new commands:
 *
 * LimeExecutable::php() automatically prepends the call with the local PHP
 * binary. You can pass the name of the PHP script to which you want to pass the
 * test file in the first parameter. If you want to execute the script directly
 * with PHP, you can leave the first parameter at its default null.
 *
 * LimeExecutable::shell() creates an command that executes a test file
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
    $command         = null,
    $inputName      = null;

  /**
   * Constructor.
   *
   * @param string $command
   * @param string $inputName
   */
  public function __construct($command, $inputName = null)
  {
    $this->command = $command;
    $this->inputName = $inputName;
  }

  /**
   * Returns the name of the command.
   *
   * @return string
   */
  public function getCommand()
  {
    return $this->command;
  }

  /**
   * Returns the name of the input used to read test file output.
   *
   * This name should be known to the used input factory configured in
   * LimeConfiguration (LimeInputFactory by default).
   *
   * @return string
   */
  public function getInputName()
  {
    return $this->inputName;
  }

  /**
   * Tries to find the system's PHP command and returns it.
   *
   * @return string
   */
  public static function php()
  {
    if (is_null(self::$php))
    {
      if (getenv('PHP_PATH'))
      {
        self::$command = getenv('PHP_PATH');

        if (!is_executable(self::$php))
        {
          throw new Exception('The defined PHP_PATH environment variable is not a valid PHP command.');
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
      foreach (array('php5', 'php') as $command)
      {
        foreach ($extensions as $extension)
        {
          foreach (explode(PATH_SEPARATOR, $path) as $dir)
          {
            $file = $dir.DIRECTORY_SEPARATOR.$command.$extension;
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
        throw new Exception("Unable to find PHP command.");
      }
    }

    return self::$php;
  }
}