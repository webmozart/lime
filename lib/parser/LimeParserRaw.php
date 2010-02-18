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

class LimeParserRaw extends LimeParser
{
  protected static
    $suppressedMethods = array('focus', 'close', 'flush');

  protected
    $error = false;

  public function parse($data)
  {
    $this->buffer .= $data;

    $lines = explode("\n", $this->buffer);

    while ($line = array_shift($lines))
    {
      if (!empty($line))
      {
        $this->error = false;

        set_error_handler(array($this, 'failedUnserialize'));
        list($method, $arguments) = unserialize($line);
        restore_error_handler();

        if ($this->error)
        {
          // prepend the line again, maybe we can unserialize later
          array_unshift($lines, $line);
          break;
        }

        if (!in_array($method, self::$suppressedMethods))
        {
          foreach ($arguments as &$argument)
          {
            if (is_string($argument))
            {
              $argument = stripcslashes($argument);
            }
          }
          call_user_func_array(array($this->output, $method), $arguments);
        }
      }
    }

    $this->buffer = implode("\n", $lines);

    $this->clearErrors();
  }

  public function done()
  {
    return empty($this->buffer);
  }

  public function failedUnserialize()
  {
    $this->error = true;
  }
}