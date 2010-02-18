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
 * Collects the logic when a test case is considered successful or incomplete.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 * @version    SVN: $Id: LimeOutputResult.php 25932 2009-12-27 19:55:32Z bschussek $
 */
class LimeLogic
{
  private
    $expected     = null,
    $actual       = 0,
    $passed       = 0,
    $failed       = 0,
    $errors       = 0,
    $warnings     = 0,
    $todos        = 0;

  /**
   * Adds the given amount of tests to the test plan.
   *
   * @param integer $plan
   */
  public function addPlan($plan)
  {
    $this->expected += $plan;
  }

  /**
   * Adds a passed test.
   */
  public function addPassed()
  {
    $this->actual++;
    $this->passed++;
  }

  /**
   * Adds a failed test.
   *
   * @param array $failure  The test failure. An array with the failure message,
   *                        the script, the line in the script and optionally
   *                        the specific error.
   */
  public function addFailed()
  {
    $this->actual++;
    $this->failed++;
  }

  /**
   * Adds a skipped test.
   */
  public function addSkipped()
  {
    $this->actual++;
    $this->passed++;
  }

  /**
   * Adds a todo.
   */
  public function addTodo()
  {
    $this->actual++;
    $this->passed++;
    $this->todos++;
  }

  /**
   * Adds a test error.
   */
  public function addError()
  {
    $this->errors++;
  }

  /**
   * Adds a test warning.
   */
  public function addWarning()
  {
    $this->warnings++;
  }

  /**
   * Returns the actual number of tests.
   *
   * @return integer
   */
  public function getActual()
  {
    return $this->actual;
  }

  /**
   * Returns the expected number of tests.
   *
   * @return integer
   */
  public function getExpected()
  {
    return is_null($this->expected) ? $this->actual : $this->expected;
  }

  /**
   * Returns the number of passed tests.
   *
   * @return integer
   */
  public function getPassed()
  {
    return $this->passed;
  }

  /**
   * Returns the number of failed tests.
   *
   * @return integer
   */
  public function getFailed()
  {
    return $this->failed;
  }

  /**
   * Returns the number of test errors.
   *
   * @return integer
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Returns the number of test warnings.
   *
   * @return integer
   */
  public function getWarnings()
  {
    return $this->warnings;
  }

  /**
   * Returns the number of todos.
   *
   * @return integer
   */
  public function getTodos()
  {
    return $this->todos;
  }

  /**
   * Returns whether not all expected tests have been executed.
   *
   * @return boolean
   */
  public function isIncomplete()
  {
    return $this->expected > 0 && $this->actual != $this->expected;
  }

  /**
   * Returns whether the test was successful.
   *
   * A test is considered successful if no test case failed, no error occurred
   * and no test is incomplete, i.e. all expected tests have been executed.
   *
   * @return boolean
   */
  public function isSuccessful()
  {
    return !$this->getErrors() && !$this->getFailed() && !$this->isIncomplete();
  }
}