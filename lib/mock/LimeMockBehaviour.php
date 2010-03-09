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
 * Provides common methods of all implemented behaviours.
 *
 * Behaviours accept the following options for initialization:
 *
 *    * strict:         If set to TRUE, the behaviour initializes all mocked
 *                      methods with the modifier strict() to enable strict
 *                      type comparison. Default: FALSE
 *    * nice:           If set to TRUE, the behaviour will ignore unexpected
 *                      method calls. Mocked methods will be initialized
 *                      with the modifier any(). Unexpected methods will be
 *                      reported as errors when verify() is called.
 *                      Default: FALSE
 *    * default_count:  The default count modifier, with which methods are
 *                      initialized. Legal values: "once", "any". Default: "once"
 *
 * @package    Lime
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 * @version    SVN: $Id: LimeMockBehaviour.php 23880 2009-11-14 10:14:34Z bschussek $
 * @see        LimeMockBehaviourInterface
 */
abstract class LimeMockBehaviour implements LimeMockBehaviourInterface
{
  protected
    $options        = array(),
    $verified       = false,
    $invocations    = array(),
    $expectNothing  = false;

  /**
   * Constructor.
   *
   * @param  array $options  The options for initializing the behaviour.
   * @return unknown_type
   */
  public function __construct(array $options = array())
  {
    $this->options = array_merge(array(
      'strict'        =>  false,
      'nice'          =>  false,
      'default_count' =>  'once',
    ), $options);
  }

  /**
   * (non-PHPdoc)
   * @see mock/LimeMockBehaviourInterface#expect($invocation)
   */
  public function expect(LimeMockInvocationExpectation $invocation)
  {
    $this->invocations[] = $invocation;

    if ($this->options['strict'])
    {
      $invocation->strict();
    }

    if ($this->options['default_count'] == 'once')
    {
      $invocation->once();
    }
    else
    {
      $invocation->any();
    }
  }

  /**
   * (non-PHPdoc)
   * @see mock/LimeMockBehaviourInterface#invoke($invocation)
   */
  public function invoke(LimeMockInvocation $invocation)
  {
    if (!$this->options['nice'] && !$this->verified && ($this->expectNothing || count($this->invocations) > 0))
    {
      throw new LimeMockInvocationException($invocation, 'was not expected to be called');
    }
  }

  /**
   * (non-PHPdoc)
   * @see mock/LimeMockBehaviourInterface#isInvokable($method)
   */
  public function isInvokable(LimeMockMethod $method)
  {
    foreach ($this->invocations as $invocation)
    {
      if ($invocation->matches($method))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * (non-PHPdoc)
   * @see mock/LimeMockBehaviourInterface#verify()
   */
  public function verify()
  {
    foreach ($this->invocations as $invocation)
    {
      $invocation->verify();
    }

    $this->verified = true;
  }

  /**
   * (non-PHPdoc)
   * @see mock/LimeMockBehaviourInterface#setExpectNothing()
   */
  public function setExpectNothing()
  {
    $this->expectNothing = true;
  }

  /**
   * (non-PHPdoc)
   * @see mock/LimeMockBehaviourInterface#reset()
   */
  public function reset()
  {
    $this->invocations = array();
  }
}