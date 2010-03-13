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
 * Base output class for outputs.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
abstract class LimeOutput implements LimeOutputInterface
{
  protected
    $total          = 0,
    $passed         = 0,
    $failed         = 0,
    $errors         = 0,
    $skipped        = 0,
    $todos          = 0,
    $files          = array(),
    $startTime      = 0,
    $time           = 0;

  private
    $file           = null;

  /**
   * Constructor.
   */
  public function __construct()
  {
    $this->startTime = microtime(true);
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#focus($file)
   */
  public function focus($file)
  {
    $this->file = $file;

    if (!isset($this->files[$file]))
    {
      $this->files[$file] = (object)array(
        'total'     => 0,
        'passed'    => 0,
        'failed'    => 0,
        'skipped'   => 0,
        'todos'     => 0,
        'errors'    => 0,
        'tests'     => array(),
        'success'   => true,
        'startTime' => microtime(true),
        'time'      => 0,
      );
    }
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#close()
   */
  public function close()
  {
    $time = microtime(true);
    $this->time = $time - $this->startTime;
    $this->files[$this->file]->time = $time - $this->files[$this->file]->startTime;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#pass($message, $file, $line)
   */
  public function pass($message, $class, $time, $file, $line)
  {
    ++$this->total;
    ++$this->passed;
    ++$this->files[$this->file]->total;
    ++$this->files[$this->file]->passed;
    $this->files[$this->file]->tests[] = array(
      'message' => $message,
      'file'    => $file,
      'line'    => $line,
      'class'   => $class,
      'time'    => $time,
      'status'  => 'success',
    );
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#fail($message, $file, $line, $error)
   */
  public function fail($message, $class, $time, $file, $line, LimeError $error = null)
  {
    ++$this->total;
    ++$this->failed;
    ++$this->files[$this->file]->total;
    ++$this->files[$this->file]->failed;
    $this->files[$this->file]->success = false;
    $this->files[$this->file]->tests[] = array(
      'message' => $message,
      'file'    => $file,
      'line'    => $line,
      'class'   => $class,
      'time'    => $time,
      'status'  => 'error',
      'error'   => $error,
    );
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#skip($message, $file, $line)
   */
  public function skip($message, $class, $time, $file, $line, $reason = '')
  {
    ++$this->total;
    ++$this->passed;
    ++$this->skipped;
    ++$this->files[$this->file]->total;
    ++$this->files[$this->file]->passed;
    ++$this->files[$this->file]->skipped;
    $this->files[$this->file]->tests[] = array(
      'message' => $message,
      'file'    => $file,
      'line'    => $line,
      'class'   => $class,
      'time'    => $time,
      'status'  => 'skipped',
      'reason'  => $reason,
    );
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#todo($message, $file, $line)
   */
  public function todo($message, $class, $file, $line)
  {
    ++$this->total;
    ++$this->passed;
    ++$this->todos;
    ++$this->files[$this->file]->total;
    ++$this->files[$this->file]->passed;
    ++$this->files[$this->file]->todos;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#error($error)
   */
  public function error(LimeError $error)
  {
    ++$this->errors;
    ++$this->files[$this->file]->errors;
    $this->files[$this->file]->success = false;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#success()
   */
  public function success()
  {
    return ($this->errors + $this->failed) == 0;
  }

  /**
   * Returns the currently focused file.
   *
   * @return string
   */
  protected function getCurrentFile()
  {
    return $this->file;
  }
}