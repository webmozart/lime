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

class LimeOutputTap extends LimeOutput implements LimeOutputInterface
{
  protected
    $configuration  = null,
    $printer        = null;

  public function __construct(LimePrinter $printer, LimeConfiguration $configuration)
  {
    parent::__construct();

    $this->printer = $printer;
    $this->configuration = $configuration;
  }

  public function supportsThreading()
  {
    return false;
  }

  private function stripBaseDir($path)
  {
    if (strpos($path, $this->configuration->getBaseDir()) == 0)
    {
      return substr($path, strlen($this->configuration->getBaseDir()));
    }
    else
    {
      return $path;
    }
  }

  public function focus($file)
  {
    if ($this->getCurrentFile() !== $file)
    {
      $this->printer->printLine('# '.$this->stripBaseDir($file), LimePrinter::INFO);
    }

    parent::focus($file);
  }

  public function close()
  {
  }

  public function pass($message, $class, $time, $file, $line)
  {
    parent::pass($message, $class, $time, $file, $line);

    if (empty($message))
    {
      $this->printer->printLine('ok '.$this->total, LimePrinter::OK);
    }
    else
    {
      $this->printer->printText('ok '.$this->total, LimePrinter::OK);
      $this->printer->printLine(' - '.$message);
    }
  }

  public function fail($message, $class, $time, $file, $line, LimeError $error = null)
  {
    parent::fail($message, $class, $time, $file, $line, $error);

    if (empty($message))
    {
      $this->printer->printLine('not ok '.$this->total, LimePrinter::NOT_OK);
    }
    else
    {
      $this->printer->printText('not ok '.$this->total, LimePrinter::NOT_OK);
      $this->printer->printLine(' - '.$message);
    }

    $this->printError($error);
  }

  public function skip($message, $class, $time, $file, $line, $reason = '')
  {
    parent::skip($message, $class, $time, $file, $line, $reason);

    if (empty($message))
    {
      $this->printer->printText('ok '.$this->total, LimePrinter::SKIP);
      $this->printer->printText(' ');
    }
    else
    {
      $this->printer->printText('ok '.$this->total, LimePrinter::SKIP);
      $this->printer->printText(' - '.$message.' ');
    }

    $this->printer->printLine('# SKIP', LimePrinter::SKIP);

    if (!empty($reason))
    {
      $this->printer->printLine('# '.$reason, LimePrinter::COMMENT);
    }
  }

  public function todo($message, $class, $file, $line)
  {
    parent::todo($message, $class, $file, $line);

    if (empty($message))
    {
      $this->printer->printText('not ok '.$this->total, LimePrinter::TODO);
      $this->printer->printText(' ');
    }
    else
    {
      $this->printer->printText('not ok '.$this->total, LimePrinter::TODO);
      $this->printer->printText(' - '.$message.' ');
    }

    $this->printer->printLine('# TODO', LimePrinter::TODO);
  }

  public function error(LimeError $error)
  {
    parent::error($error);

    $this->printError($error);
  }

  private function printError(LimeError $error)
  {
    $message = sprintf("%s: %s", $error->getType(), $error->getMessage());

    if ($error->getFile())
    {
      $message .= sprintf("\n(in %s on line %s)", $this->stripBaseDir($error->getFile()), $error->getLine());
    }

    if ($error->getType() == 'Warning' || $error->getType() == 'Notice')
    {
      $lineStyle = LimePrinter::WARNING;
    }
    else
    {
      $lineStyle = LimePrinter::ERROR;
    }

    $this->printer->printLargeBox($message, $lineStyle);

    if (is_readable($error->getFile()))
    {
      $this->printer->printLine('Source code:', LimePrinter::COMMENT);

      $file = fopen($error->getFile(), 'r');
      $indentation = strlen($error->getLine()+5) + 4;
      $i = 1; $l = $error->getLine() - 2;
      while ($i < $l)
      {
        fgets($file);
        ++$i;
      }
      while ($i < $l+5)
      {
        $line = rtrim(fgets($file), "\n");
        $line = '  '.$i.'. '.wordwrap($line, 80 - $indentation, "\n".str_repeat(' ', $indentation));
        $lines = explode("\n", $line);
        $style = ($i == $error->getLine()) ? $lineStyle : null;

        foreach ($lines as $line)
        {
          $this->printer->printLine(str_pad($line, 80, ' '), $style);
        }

        ++$i;
      }
      fclose($file);

      $this->printer->printLine('');
    }

    if (count($error->getInvocationTrace()) > 0)
    {
      $this->printer->printLine('Invocation trace:', LimePrinter::COMMENT);

      foreach ($error->getInvocationTrace() as $i => $trace)
      {
        $this->printer->printLine('  '.($i+1).') '.$trace);
      }

      $this->printer->printLine('');
    }

    if (count($error->getTrace()) > 0)
    {
      $this->printer->printLine('Exception trace:', LimePrinter::COMMENT);

      $this->printTrace(null, $error->getFile(), $error->getLine());

      foreach ($error->getTrace() as $trace)
      {
        if (array_key_exists('class', $trace))
        {
          $method = sprintf('%s%s%s()', $trace['class'], $trace['type'], $trace['function']);
        }
        else
        {
          $method = sprintf('%s()', $trace['function']);
        }

        if (array_key_exists('file', $trace))
        {
          $this->printTrace($method, $trace['file'], $trace['line']);
        }
        else
        {
          $this->printTrace($method);
        }
      }

      $this->printer->printLine('');
    }
  }

  private function printTrace($method = null, $file = null, $line = null)
  {
    if (!is_null($method))
    {
      $method .= ' ';
    }

    $this->printer->printText('  '.$method.'at ');

    if (!is_null($file) && !is_null($line))
    {
      $this->printer->printText($this->stripBaseDir($file), LimePrinter::TRACE);
      $this->printer->printText(':');
      $this->printer->printLine($line, LimePrinter::TRACE);
    }
    else
    {
      $this->printer->printLine('[internal function]');
    }
  }

  public function info($message)
  {
    $this->printer->printLine('# '.$message, LimePrinter::INFO);
  }

  public function comment($message)
  {
    $this->printer->printLine('# '.$message, LimePrinter::COMMENT);
  }

  public function getMessages($total, $passed, $errors, $todos)
  {
    $messages = array();

    if ($passed === $total && $errors == 0)
    {
      if ($todos > 0)
      {
        $messages[] = array(sprintf('Looks like there are %s TODOs open.', $todos), LimePrinter::HAPPY);
      }
      else
      {
        $messages[] = array('Looks like everything went fine.', LimePrinter::HAPPY);
      }
    }
    else if ($passed != $total)
    {
      $messages[] = array(sprintf('Looks like you failed %s tests of %s.', $total - $passed, $total), LimePrinter::ERROR);
    }
    else if ($errors > 0)
    {
      $messages[] = array('Looks like some errors occurred.', LimePrinter::ERROR);
    }

    return $messages;
  }

  public function flush()
  {
    $this->printer->printLine('1..'.$this->total);

    $messages = $this->getMessages($this->total, $this->passed, $this->errors, $this->todos);

    foreach ($messages as $message)
    {
      list ($message, $style) = $message;

      $this->printer->printBox('# '.$message, $style);
    }
  }
}