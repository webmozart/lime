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

class LimeOutputRaw implements LimeOutputInterface
{
  protected
    $errors      = 0,
    $failed    = 0;

  protected function printCall($method, array $arguments = array())
  {
    foreach ($arguments as &$argument)
    {
      if (is_string($argument))
      {
        $argument = str_replace(array("\n", "\r"), array('\n', '\r'), $argument);
      }
    }

    print serialize(array($method, $arguments))."\n";
  }

  public function supportsThreading()
  {
    return true;
  }

  public function focus($file)
  {
    $this->printCall('focus', array($file));
  }

  public function close()
  {
    $this->printCall('close', array());
  }

  public function pass($message, $class, $time, $file, $line)
  {
    $this->printCall('pass', array($message, $class, $time, $file, $line));
  }

  public function fail($message, $class, $time, $file, $line, LimeError $error = null)
  {
    ++$this->failed;
    $this->printCall('fail', array($message, $class, $time, $file, $line, $error));
  }

  public function skip($message, $class, $time, $file, $line, $reason = '')
  {
    $this->printCall('skip', array($message, $class, $time, $file, $line, $reason));
  }

  public function todo($message, $class, $file, $line)
  {
    $this->printCall('todo', array($message, $class, $file, $line));
  }

  public function error(LimeError $error)
  {
    ++$this->errors;
    $this->printCall('error', array($error));
  }

  public function comment($message)
  {
    $this->printCall('comment', array($message));
  }

  public function flush()
  {
    $this->printCall('flush');
  }

  public function success()
  {
    return ($this->errors + $this->failed) == 0;
  }
}