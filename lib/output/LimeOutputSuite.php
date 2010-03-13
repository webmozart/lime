<?php

/*
 * This file is part of the Lime test framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Bernhard Schussek <bernhard.schussek@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Colorizes test results and summarizes them in the console.
 *
 * For each test file, one line is printed in the console with a few optional
 * lines in case the file contains errors or failed tests.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 * @version    SVN: $Id: LimeOutputSuite.php 28080 2010-02-17 14:58:44Z bschussek $
 */
class LimeOutputSuite extends LimeOutput
{
  protected
    $loader         = null,
    $printer        = null,
    $configuration  = null,
    $_errors        = array(),
    $_failures      = array(),
    $_todos         = array();

  /**
   * Constructor.
   *
   * @param LimePrinter $printer              The printer for printing text to the console
   * @param LimeConfiguration $configuration  The configuration of this output
   */
  public function __construct(LimePrinter $printer, LimeConfiguration $configuration)
  {
    parent::__construct();

    $this->printer = $printer;
    $this->configuration = $configuration;
  }

  public function setLoader(LimeLoader $loader)
  {
    $this->loader = $loader;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#supportsThreading()
   */
  public function supportsThreading()
  {
    return true;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutput#focus($file)
   */
  public function focus($file)
  {
    parent::focus($file);

    if (!isset($this->_errors[$file]))
    {
      $this->_errors[$file] = array();
      $this->_failures[$file] = array();
      $this->_todos[$file] = array();
    }
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutput#fail($message, $file, $line, $error)
   */
  public function fail($message, $class, $time, $file, $line, LimeError $error = null)
  {
    parent::fail($message, $class, $time, $file, $line, $error);

    $this->_failures[$this->getCurrentFile()][$this->files[$this->getCurrentFile()]->total] = array($message, $class, $time, $error);
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutput#todo($message, $file, $line)
   */
  public function todo($message, $class, $file, $line)
  {
    parent::todo($message, $class, $file, $line);

    $this->_todos[$this->getCurrentFile()][] = $message;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutput#error($error)
   */
  public function error(LimeError $error)
  {
    parent::error($error);

    $this->_errors[$this->getCurrentFile()][] = $error;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#close()
   */
  public function close()
  {
    parent::close();

    if (!is_null($file = $this->getCurrentFile()))
    {
      $path = $this->truncate($file);
      $prefix = '';

      if (!is_null($this->loader))
      {
        $labels = $this->loader->getFileByPath($file)->getLabels();
        if (count($labels) > 0)
        {
          $prefix = '['.implode(',',$labels).'] ';
        }
      }

      if (strlen($path) > (71 - strlen($prefix)))
      {
        $path = substr($path, -(71 - strlen($prefix)));
      }

      if ($prefix)
      {
        $this->printer->printText(trim($prefix), LimePrinter::LABEL);
        $path = ' '.$path;
      }

      $this->printer->printText(str_pad($path, 73 - strlen($prefix), '.'));

      if (!$this->files[$file]->success)
      {
        $this->printer->printLine("not ok", LimePrinter::NOT_OK);
      }
      else
      {
        $this->printer->printLine("ok", LimePrinter::OK);
      }

      if (count($this->_failures[$file]))
      {
        $this->printer->printLine('    Failed Tests:', LimePrinter::COMMENT);

        $i = 0;
        foreach ($this->_failures[$file] as $number => $failed)
        {
          if (!$this->configuration->getVerbose() && $i > 2)
          {
            $this->printer->printLine(sprintf('    ... and %s more', count($this->_failures[$file])-$i));
            break;
          }

          $this->printer->printLine('    not ok '.$number.' - '.$failed[0]);
          ++$i;
        }
      }

      if (count($this->_errors[$file]))
      {
        $this->printer->printLine('    Errors:', LimePrinter::COMMENT);

        foreach ($this->_errors[$file] as $i => $error)
        {
          if (!$this->configuration->getVerbose() && $i > 2)
          {
            $this->printer->printLine(sprintf('    ... and %s more', count($this->_errors[$file])-$i));
            break;
          }

          $this->printer->printLine('    '.$error->getMessage());

          if ($this->configuration->getVerbose())
          {
            $this->printer->printText('      (in ');
            $this->printer->printText($this->truncate($error->getFile()), LimePrinter::TRACE);
            $this->printer->printText(' on line ');
            $this->printer->printText($error->getLine(), LimePrinter::TRACE);
            $this->printer->printLine(')');
          }
        }
      }

      if (count($this->_todos[$file]))
      {
        $this->printer->printLine('    TODOs:', LimePrinter::COMMENT);

        foreach ($this->_todos[$file] as $i => $todo)
        {
          if (!$this->configuration->getVerbose() && $i > 2)
          {
            $this->printer->printLine(sprintf('    ... and %s more', count($this->_todos[$file])-$i));
            break;
          }

          $this->printer->printLine('    '.$todo);
        }
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#comment($message)
   */
  public function comment($message) {}

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#flush()
   */
  public function flush()
  {
    $failedFiles = 0;
    $actualFiles = count($this->files);

    foreach ($this->files as $file)
    {
      if (!$file->success)
      {
        ++$failedFiles;
      }
    }

    if ($failedFiles > 0)
    {
      $stats = sprintf(' Failed %d/%d test scripts, %.2f%% okay. %d/%d subtests failed, %.2f%% okay.',
          $failedFiles, $actualFiles, 100 - 100*$failedFiles/max(1,$actualFiles),
          $this->failed, $this->total, 100 - 100*$this->failed/max(1,$this->total));

      $this->printer->printBox($stats, LimePrinter::NOT_OK);
    }
    else
    {
      $time = max(1, round($this->time));
      $stats = sprintf(' Files=%d, Tests=%d, Time=%02d:%02d, Processes=%d',
          $actualFiles, $this->total, floor($time/60), $time%60, $this->configuration->getProcesses());

      $this->printer->printBox(' All tests successful.', LimePrinter::HAPPY);
      $this->printer->printBox($stats, LimePrinter::HAPPY);
    }
  }

  /**
   * Removes the configured suffix and the path from the filename.
   *
   * @param  string $file
   * @return string
   */
  protected function truncate($file)
  {
    return basename($file, $this->configuration->getSuffix());
  }
}