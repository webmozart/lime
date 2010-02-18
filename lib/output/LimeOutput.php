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
 * Base output class for outputs that rely on LimeLogicCollection.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
abstract class LimeOutput extends LimeLogicCollection implements LimeOutputInterface
{
  private
    $file           = null;

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#focus($file)
   */
  public function focus($file)
  {
    $this->file = $file;
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#plan($amount)
   */
  public function plan($amount)
  {
    $this[$this->file]->addPlan($amount);
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#pass($message, $file, $line)
   */
  public function pass($message, $file, $line)
  {
    $this[$this->file]->addPassed();
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#fail($message, $file, $line, $error)
   */
  public function fail($message, $file, $line, $error = null)
  {
    $this[$this->file]->addFailed();
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#skip($message, $file, $line)
   */
  public function skip($message, $file, $line)
  {
    $this[$this->file]->addSkipped();
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#todo($message, $file, $line)
   */
  public function todo($message, $file, $line)
  {
    $this[$this->file]->addTodo();
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#warning($message, $file, $line)
   */
  public function warning($message, $file, $line)
  {
    $this[$this->file]->addWarning();
  }

  /**
   * (non-PHPdoc)
   * @see lib/output/LimeOutputInterface#error($error)
   */
  public function error(LimeError $error)
  {
    $this[$this->file]->addError();
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