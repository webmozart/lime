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
 * Collects the logic when a test suite is considered successful or incomplete.
 *
 * Internally, this is a collection of LimeLogic instances.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 * @version    SVN: $Id: LimeOutputResult.php 25932 2009-12-27 19:55:32Z bschussek $
 */
class LimeLogicCollection implements ArrayAccess, Countable, IteratorAggregate
{
  private
    $logics       = array();

  /**
   * Returns the actual number of tests.
   *
   * @return integer
   */
  public function getActual()
  {
    $actual = 0;

    foreach ($this->logics as $logic)
    {
      $actual += $logic->getActual();
    }

    return $actual;
  }

  /**
   * Returns the expected number of tests.
   *
   * @return integer
   */
  public function getExpected()
  {
    $expected = 0;

    foreach ($this->logics as $logic)
    {
      $expected += $logic->getExpected();
    }

    return $expected;
  }

  /**
   * Returns the number of passed tests.
   *
   * @return integer
   */
  public function getPassed()
  {
    $passed = 0;

    foreach ($this->logics as $logic)
    {
      $passed += $logic->getPassed();
    }

    return $passed;
  }

  /**
   * Returns the number of failed tests.
   *
   * @return integer
   */
  public function getFailed()
  {
    $failed = 0;

    foreach ($this->logics as $logic)
    {
      $failed += $logic->getFailed();
    }

    return $failed;
  }

  /**
   * Returns the number of test errors.
   *
   * @return integer
   */
  public function getErrors()
  {
    $errors = 0;

    foreach ($this->logics as $logic)
    {
      $errors += $logic->getErrors();
    }

    return $errors;
  }

  /**
   * Returns the number of test warnings.
   *
   * @return integer
   */
  public function getWarnings()
  {
    $warnings = 0;

    foreach ($this->logics as $logic)
    {
      $warnings += $logic->getWarnings();
    }

    return $warnings;
  }

  /**
   * Returns the number of todos.
   *
   * @return integer
   */
  public function getTodos()
  {
    $todos = 0;

    foreach ($this->logics as $logic)
    {
      $todos += $logic->getTodos();
    }

    return $todos;
  }

  /**
   * Returns whether not all expected tests have been executed in any logic.
   *
   * @return boolean
   */
  public function isIncomplete()
  {
    foreach ($this->logics as $logic)
    {
      if ($logic->isIncomplete())
      {
        return true;
      }
    }

    return false;
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
    foreach ($this->logics as $logic)
    {
      if ($logic->isSuccessful())
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Returns the LimeLogic instance for the given name.
   *
   * @param  string $name
   * @return LimeLogic
   */
  public function offsetGet($name)
  {
    if (!isset($this->logics[$name]))
    {
      $this->logics[$name] = new LimeLogic();
    }

    return $this->logics[$name];
  }

  /**
   * Returns whether the LimeLogic instance with the given name exists.
   *
   * @param  string $name
   * @return boolean
   */
  public function offsetExists($name)
  {
    return isset($this->logics[$name]);
  }

  /**
   * Not supported.
   */
  public function offsetSet($name, $value)
  {
    throw new LogicException('Setting of values is not supported');
  }

  /**
   * Not supported.
   */
  public function offsetUnset($name)
  {
    throw new LogicException('Deleting of values is not supported');
  }

  /**
   * Returns the iterator for this class.
   *
   * @return ArrayIterator
   */
  public function getIterator()
  {
    return new ArrayIterator($this->logics);
  }

  /**
   * Returns the number of logics in this collection.
   *
   * @return integer
   */
  public function count()
  {
    return count($this->logics);
  }

  /**
   * Returns the number of failed logics in this collection.
   *
   * @return integer
   */
  public function countFailed()
  {
    $count = 0;

    foreach ($this->logics as $logic)
    {
      if (!$logic->isSuccessful())
      {
        ++$count;
      }
    }

    return $count;
  }
}