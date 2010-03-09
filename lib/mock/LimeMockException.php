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
 * An exception thrown from mock objects.
 *
 * This exception is generally thrown if you invoke an unexpected method on
 * a mock object while in replay mode. Generally this exception should bubble
 * up to notify the user about test errors.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 * @version    SVN: $Id: LimeMockException.php 23701 2009-11-08 21:23:40Z bschussek $
 */
class LimeMockException extends Exception
{
  protected
    $invocation        = null,
    $mock              = null;

  /**
   * Constructor.
   *
   * @param LimeMockInvocationException $e
   */
  public function __construct(LimeMockInterface $mock, LimeMockInvocationException $e)
  {
    parent::__construct($e->getMessage());

    $this->invocation = $e->getInvocation();
    $this->mock = $mock;
  }

  /**
   * Returns the mock that threw this exception.
   *
   * @return LimeMockInterface
   */
  public function getMock()
  {
    return $this->mock;
  }

  /**
   * Returns the class of the invoked method that caused this exception.
   *
   * @return string
   */
  public function getClass()
  {
    return $this->invocation->getClass();
  }

  /**
   * Returns the method that caused this exception.
   *
   * @return string
   */
  public function getMethod()
  {
    return $this->invocation->getMethod();
  }

  /**
   * Returns the invocationt race.
   *
   * @return LimeMockInvocationTrace
   */
  public function getInvocationTrace()
  {
    return $this->mock->__lime_getInvocationTrace();
  }
}