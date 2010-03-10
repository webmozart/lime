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
 * Unit test library.
 *
 * @package    lime
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id: LimeTest.php 28111 2010-02-18 16:46:32Z bschussek $
 */
class LimeTest
{
  protected
    $output                 = null,
    $errorReporting         = true,
    $comment                = '',
    $exception              = null,
    $exceptionExpectation   = null,
    $mocks                  = array(),
    $failed                 = false;

  public function __construct(LimeConfiguration $configuration = null)
  {
    if (is_null($configuration))
    {
      $configuration = LimeConfiguration::getInstance(getcwd());
    }

    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    $this->output = $configuration->getTestOutput();
    $this->output->focus($file);

    set_error_handler(array($this, 'handleError'));

    // make sure that exceptions that are not caught by the test runner are
    // caught and formatted in an appropriate way

    // UPDATE: should be obsolete because uncaught exceptions are now handled
    // by the CLI
//    set_exception_handler(array($this, 'handleException'));
  }

  public function beginTest($comment = '')
  {
    $this->comment = $comment;
    $this->exception = null;
    $this->exceptionExpectation = null;
    $this->mocks = array();
    $this->failed = false;
  }

  public function endTest()
  {
    if (!is_null($this->exceptionExpectation))
    {
      $expected = $this->exceptionExpectation->getException();
      $file = $this->exceptionExpectation->getFile();
      $line = $this->exceptionExpectation->getLine();

      if (is_string($expected))
      {
        $actual = is_object($this->exception) ? get_class($this->exception) : 'none';
        $message = sprintf('A "%s" was thrown', $expected);
      }
      else
      {
        $actual = $this->exception;
        $message = sprintf('A "%s" was thrown', get_class($expected));
      }

      try
      {
        $this->is($actual, $expected, $message);
      }
      catch (LimeConstraintException $e)
      {
        $this->printError(LimeError::fromException($e, $file, $line, array()));
      }
    }

    if (!$this->failed)
    {
      try
      {
        foreach ($this->mocks as $mock)
        {
          $mock->verify();
        }

        list ($file, $line) = LimeTrace::findCaller('LimeTest');

        $this->output->pass($this->comment, $file, $line);
      }
      catch (LimeMockException $e)
      {
        $this->printError(LimeError::fromException($e, '', '', array()));
      }
    }
  }

  public function setErrorReporting($enabled)
  {
    $this->errorReporting = $enabled;
  }

  public function __destruct()
  {
    $this->output->close();
    $this->output->flush();

    restore_error_handler();
    restore_exception_handler();
  }

  public function getOutput()
  {
    return $this->output;
  }

  private function test(LimeConstraintInterface $constraint, $value, $message)
  {
    try
    {
      $constraint->evaluate($value);
    }
    catch (LimeConstraintException $e)
    {
      throw new LimeConstraintException($message."\n".$e->getMessage());
    }
  }

  /**
   * Tests a condition and passes if it is true
   *
   * @param mixed  $exp     condition to test
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function ok($exp, $message = '')
  {
    if (!(boolean)$exp)
    {
      throw new LimeConstraintException($message);
    }
  }

  /**
   * Compares two values and passes if they are equal (==)
   *
   * @param mixed  $exp1    left value
   * @param mixed  $exp2    right value
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function is($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintIs($exp2), $exp1, $message);
  }

  /**
   * Compares two values and passes if they are identical (===)
   *
   * @param mixed  $exp1    left value
   * @param mixed  $exp2    right value
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function same($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintSame($exp2), $exp1, $message);
  }

  /**
   * Compares two values and passes if they are not equal
   *
   * @param mixed  $exp1    left value
   * @param mixed  $exp2    right value
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function isnt($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintIsNot($exp2), $exp1, $message);
  }

  /**
   * Compares two values and passes if they are not identical (!==)
   *
   * @param mixed  $exp1    left value
   * @param mixed  $exp2    right value
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function isntSame($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintNotSame($exp2), $exp1, $message);
  }

  /**
   * Tests a string against a regular expression
   *
   * @param string $exp     value to test
   * @param string $regex   the pattern to search for, as a string
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function like($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintLike($exp2), $exp1, $message);
  }

  /**
   * Checks that a string doesn't match a regular expression
   *
   * @param string $exp     value to test
   * @param string $regex   the pattern to search for, as a string
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function unlike($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintUnlike($exp2), $exp1, $message);
  }

  public function greaterThan($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintGreaterThan($exp2), $exp1, $message);
  }

  public function greaterThanEqual($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintGreaterThanEqual($exp2), $exp1, $message);
  }

  public function lessThan($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintLessThan($exp2), $exp1, $message);
  }

  public function lessThanEqual($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintLessThanEqual($exp2), $exp1, $message);
  }

  public function contains($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintContains($exp2), $exp1, $message);
  }

  public function containsNot($exp1, $exp2, $message = '')
  {
    return $this->test(new LimeConstraintContainsNot($exp2), $exp1, $message);
  }

  /**
   * Always fails
   *
   * @param string $message display output message
   *
   * @return false
   */
  public function fail($message)
  {
    throw new LimeConstraintException($message);
  }

  /**
   * Outputs a diag message but runs no test
   *
   * @param string $message display output message
   *
   * @return void
   */
  public function diag($message)
  {
    $this->output->comment($message);
  }

  /**
   * Counts as $nbTests tests--useful for conditional tests
   *
   * @param string  $message  display output message
   * @param integer $nbTests number of tests to skip
   *
   * @return void
   */
  public function skip($nbTests = 1, $message = '')
  {
    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    for ($i = 0; $i < $nbTests; $i++)
    {
      $this->output->skip($message, $file, $line);
    }
  }

  /**
   * Counts as a test--useful for tests yet to be written
   *
   * @param string $message display output message
   *
   * @return void
   */
  public function todo($message = '')
  {
    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    $this->output->todo($message, $file, $line);
  }

  public function comment($message)
  {
    $this->output->comment($message);
  }

  public function mock($class, array $options = array())
  {
    $mock = LimeMock::create($class, $options);

    $this->mocks[] = $mock;

    return $mock;
  }

  public function stub($class, array $options = array())
  {
    $options = array_merge(array(
      'nice'            =>  true,
      'default_count'   =>  'any',
    ), $options);

    return LimeMock::create($class, $options);
  }

  public function extendMock($class, array $options = array())
  {
    $options['stub_methods'] = false;

    return $this->mock($class, $options);
  }

  public function extendStub($class, array $options = array())
  {
    $options['stub_methods'] = false;

    return $this->stub($class, $options);
  }

  public function expect($exception, $code = null)
  {
    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    $this->exceptionExpectation = new LimeExceptionExpectation($exception, $file, $line);
    $this->exception = null;
  }

  public function handleError($code, $message, $file, $line, $context)
  {
    if (!$this->errorReporting || ($code & error_reporting()) == 0)
    {
      return false;
    }

    switch ($code)
    {
      case E_WARNING:
        $message = 'Warning: '.$message;
        break;
      case E_NOTICE:
        $message = 'Notice: '.$message;
        break;
    }

    $this->failed = true;
    $this->output->fail($this->comment, $file, $line);
    $this->output->warning($message, $file, $line);
  }

  public function handleException(Exception $exception)
  {
    if (!is_null($this->exceptionExpectation))
    {
      $this->exception = $exception;
    }
    else
    {
      $this->printError(LimeError::fromException($exception));
    }

    // exception was handled
    return true;
  }

  protected function printError(LimeError $error)
  {
    $this->failed = true;
    $this->output->fail($this->comment, $error->getFile(), $error->getLine());
    $this->output->error($error);
  }
}