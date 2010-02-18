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
    $startTime      = 0,
    $errors         = array(),
    $failures       = array(),
    $warnings       = array(),
    $todos          = array();

  /**
   * Constructor.
   *
   * @param LimePrinter $printer              The printer for printing text to the console
   * @param LimeConfiguration $configuration  The configuration of this output
   */
  public function __construct(LimePrinter $printer, LimeConfiguration $configuration)
  {
    $this->printer = $printer;
    $this->configuration = $configuration;
    $this->startTime = time();
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

    if (!isset($this->errors[$file]))
    {
      $this->errors[$file] = array();
      $this->failures[$file] = array();
      $this->warnings[$file] = array();
      $this->todos[$file] = array();
    }
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutput#fail($message, $file, $line, $error)
   */
  public function fail($message, $file, $line, $error = null)
  {
    parent::fail($message, $file, $line, $error);

    $this->failures[$this->getCurrentFile()][$this[$this->getCurrentFile()]->getActual()] = array($message, $file, $line, $error);
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutput#todo($message, $file, $line)
   */
  public function todo($message, $file, $line)
  {
    parent::todo($message, $file, $line);

    $this->todos[$this->getCurrentFile()][] = $message;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutput#warning($message, $file, $line)
   */
  public function warning($message, $file, $line)
  {
    parent::warning($message, $file, $line);

    $this->warnings[$this->getCurrentFile()][] = array($message, $file, $line);
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutput#error($error)
   */
  public function error(LimeError $error)
  {
    parent::error($error);

    $this->errors[$this->getCurrentFile()][] = $error;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#close()
   */
  public function close()
  {
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

      if (!$this[$file]->isSuccessful())
      {
        $this->printer->printLine("not ok", LimePrinter::NOT_OK);
      }
      else if ($this[$file]->getWarnings())
      {
        $this->printer->printLine("warning", LimePrinter::WARNING);
      }
      else
      {
        $this->printer->printLine("ok", LimePrinter::OK);
      }

      if ($this[$file]->isIncomplete())
      {
        $this->printer->printLine('    Plan Mismatch:', LimePrinter::COMMENT);
        if ($this[$file]->getActual() > $this[$file]->getExpected())
        {
          $this->printer->printLine(sprintf('    Looks like you only planned %s tests but ran %s.', $this[$file]->getExpected(), $this[$file]->getActual()));
        }
        else
        {
          $this->printer->printLine(sprintf('    Looks like you planned %s tests but only ran %s.', $this[$file]->getExpected(), $this[$file]->getActual()));
        }
      }

      if (count($this->failures[$file]))
      {
        $this->printer->printLine('    Failed Tests:', LimePrinter::COMMENT);

        $i = 0;
        foreach ($this->failures[$file] as $number => $failed)
        {
          if (!$this->configuration->getVerbose() && $i > 2)
          {
            $this->printer->printLine(sprintf('    ... and %s more', count($this->failures[$file])-$i));
            break;
          }

          $this->printer->printLine('    not ok '.$number.' - '.$failed[0]);
          ++$i;
        }
      }

      if (count($this->warnings[$file]))
      {
        $this->printer->printLine('    Warnings:', LimePrinter::COMMENT);

        foreach ($this->warnings[$file] as $i => $warning)
        {
          if (!$this->configuration->getVerbose() && $i > 2)
          {
            $this->printer->printLine(sprintf('    ... and %s more', count($this->warnings[$file])-$i));
            break;
          }

          $this->printer->printLine('    '.$warning[0]);

          if ($this->configuration->getVerbose())
          {
            $this->printer->printText('      (in ');
            $this->printer->printText($this->truncate($warning[1]), LimePrinter::TRACE);
            $this->printer->printText(' on line ');
            $this->printer->printText($warning[2], LimePrinter::TRACE);
            $this->printer->printLine(')');
          }
        }
      }

      if (count($this->errors[$file]))
      {
        $this->printer->printLine('    Errors:', LimePrinter::COMMENT);

        foreach ($this->errors[$file] as $i => $error)
        {
          if (!$this->configuration->getVerbose() && $i > 2)
          {
            $this->printer->printLine(sprintf('    ... and %s more', count($this->errors[$file])-$i));
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

      if (count($this->todos[$file]))
      {
        $this->printer->printLine('    TODOs:', LimePrinter::COMMENT);

        foreach ($this->todos[$file] as $i => $todo)
        {
          if (!$this->configuration->getVerbose() && $i > 2)
          {
            $this->printer->printLine(sprintf('    ... and %s more', count($this->todos[$file])-$i));
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
    $failedFiles = $this->countFailed();
    $actualFiles = $this->count();

    if ($failedFiles > 0)
    {
      $failedTests = $this->getFailed();
      $expectedTests = $this->getExpected();

      $stats = sprintf(' Failed %d/%d test scripts, %.2f%% okay. %d/%d subtests failed, %.2f%% okay.',
          $failedFiles, $actualFiles, 100 - 100*$failedFiles/max(1,$actualFiles),
          $failedTests, $expectedTests, 100 - 100*$failedTests/max(1,$expectedTests));

      $this->printer->printBox($stats, LimePrinter::NOT_OK);
    }
    else
    {
      $actualTests = $this->getActual();

      $time = max(1, time() - $this->startTime);
      $stats = sprintf(' Files=%d, Tests=%d, Time=%02d:%02d, Processes=%d',
          $actualFiles, $actualTests, floor($time/60), $time%60, $this->configuration->getProcesses());

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