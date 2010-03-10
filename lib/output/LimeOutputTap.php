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

  public function pass($message, $file, $line)
  {
    parent::pass($message, $file, $line);

    if (empty($message))
    {
      $this->printer->printLine('ok '.$this->getTotal(), LimePrinter::OK);
    }
    else
    {
      $this->printer->printText('ok '.$this->getTotal(), LimePrinter::OK);
      $this->printer->printLine(' - '.$message);
    }
  }

  public function fail($message, $file, $line, $error = null)
  {
    parent::fail($message, $file, $line, $error);

    if (empty($message))
    {
      $this->printer->printLine('not ok '.$this->getTotal(), LimePrinter::NOT_OK);
    }
    else
    {
      $this->printer->printText('not ok '.$this->getTotal(), LimePrinter::NOT_OK);
      $this->printer->printLine(' - '.$message);
    }

    if (!is_null($error))
    {
      foreach (explode("\n", $error) as $line)
      {
        $this->printer->printLine('#       '.$line, LimePrinter::COMMENT);
      }
    }
  }

  public function skip($message, $file, $line)
  {
    parent::skip($message, $file, $line);

    if (empty($message))
    {
      $this->printer->printText('ok '.$this->getTotal(), LimePrinter::SKIP);
      $this->printer->printText(' ');
    }
    else
    {
      $this->printer->printText('ok '.$this->getTotal(), LimePrinter::SKIP);
      $this->printer->printText(' - '.$message.' ');
    }

    $this->printer->printLine('# SKIP', LimePrinter::SKIP);
  }

  public function todo($message, $file, $line)
  {
    parent::todo($message, $file, $line);

    if (empty($message))
    {
      $this->printer->printText('not ok '.$this->getTotal(), LimePrinter::TODO);
      $this->printer->printText(' ');
    }
    else
    {
      $this->printer->printText('not ok '.$this->getTotal(), LimePrinter::TODO);
      $this->printer->printText(' - '.$message.' ');
    }

    $this->printer->printLine('# TODO', LimePrinter::TODO);
  }

  public function warning($message, $file, $line)
  {
    parent::warning($message, $file, $line);

    $message .= sprintf("\n(in %s on line %s)", $this->stripBaseDir($file), $line);

    $this->printer->printLargeBox($message, LimePrinter::WARNING);
  }

  public function error(LimeError $error)
  {
    parent::error($error);

    $message = sprintf("%s: %s\n(in %s on line %s)", $error->getType(),
        $error->getMessage(), $this->stripBaseDir($error->getFile()), $error->getLine());

    $this->printer->printLargeBox($message, LimePrinter::ERROR);

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
        $style = ($i == $error->getLine()) ? LimePrinter::ERROR : null;

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

  public function getMessages($total, $passed, $errors, $warnings)
  {
    $messages = array();

    if ($passed === $total && $errors == 0)
    {
      if ($warnings > 0)
      {
        $messages[] = array('Looks like you\'re nearly there.', LimePrinter::WARNING);
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
    $this->printer->printLine('1..'.$this->getTotal());

    $messages = $this->getMessages($this->getTotal(), $this->getPassed(), $this->getErrors(), $this->getWarnings());

    foreach ($messages as $message)
    {
      list ($message, $style) = $message;

      $this->printer->printBox(' '.$message, $style);
    }
  }
}