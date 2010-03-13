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
    $class                  = '',
    $file                   = '',
    $line                   = null,
    $comment                = '',
    $exception              = null,
    $exceptionExpectation   = null,
    $mocks                  = array(),
    $failed                 = false,
    $skipped                = false,
    $aborted                = false,
    $startTime              = 0;

  public function __construct(LimeConfiguration $configuration = null)
  {
    if (is_null($configuration))
    {
      $configuration = LimeConfiguration::read(getcwd());
    }

    $this->output = $configuration->getTestOutput();

    set_error_handler(array($this, 'handleError'));

    // make sure that exceptions that are not caught by the test runner are
    // caught and formatted in an appropriate way

    // UPDATE: should be obsolete because uncaught exceptions are now handled
    // by the CLI
//    set_exception_handler(array($this, 'handleException'));
  }

  public function beginTest($comment, $file, $line)
  {
    $this->comment = $comment;
    $this->file = $file;
    $this->line = $line;
    $this->error = null;
    $this->exception = null;
    $this->exceptionExpectation = null;
    $this->mocks = array();
    $this->failed = false;
    $this->skipped = false;
    $this->aborted = false;
    $this->startTime = microtime(true);

    $file = preg_replace('/~annotated$/', '', $file);

    $this->output->focus($file);
  }

  public function endTest()
  {
    $time = microtime(true) - $this->startTime;
    $this->startTime = 0;

    if ($this->skipped)
    {
      $this->output->skip($this->comment, $this->class, $time, $this->skipped[0], $this->skipped[1], $this->skipped[2]);

      return;
    }

    if (!is_null($this->exceptionExpectation) && !$this->aborted)
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
        $this->failed = true;
        $this->error = LimeError::fromException($e, $file, $line, array());
      }
    }

    if (is_null($this->exceptionExpectation) && !is_null($this->exception))
    {
      $this->failed = true;
      $this->error = LimeError::fromException($this->exception);
    }

    if (!$this->failed)
    {
      try
      {
        foreach ($this->mocks as $mock)
        {
          $mock->verify();
        }

        $this->output->pass($this->comment, $this->class, $time, $this->file, $this->line);
      }
      catch (LimeMockException $e)
      {
        $this->failed = true;
        // suppress trace
        $this->error = LimeError::fromException($e, '', '', array());
      }
    }

    if ($this->failed)
    {
      $this->output->fail($this->comment, $this->class, $time, $this->file, $this->line, $this->error);
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
   * Skips the current test
   */
  public function skip($reason = '')
  {
    $this->skipped = $this->findCaller();
    $this->skipped[] = $reason;

    throw new Exception();
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
    list ($file, $line) = $this->findCaller();

    $this->output->todo($message, $this->class, $file, $line);
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
    list ($file, $line) = $this->findCaller();

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
        $type = 'Warning';
        break;
      default:
        $type = 'Notice';
        break;
    }

    $trace = debug_backtrace();
    array_shift($trace); // handleError() is not important

    $this->failed = true;
    $this->aborted = true;
    $this->error = new LimeError($message, $file, $line, $type, $trace);

    if ($this->startTime > 0)
    {
      // abort test execution
      throw new Exception($message);
    }
    else
    {
      $this->output->error($this->error);
    }
  }

  public function handleException(Exception $exception)
  {
    if (!$this->skipped && !$this->aborted)
    {
      $this->exception = $exception;
    }

    // exception was handled
    return true;
  }

  protected function findCaller()
  {
    $traces = debug_backtrace();
    $result = array($traces[0]['file'], $traces[0]['line']);

    for ($i = count($traces)-1; $i >= 0; --$i)
    {
      if (isset($traces[$i]['object']) && isset($traces[$i]['file']) && isset($traces[$i]['line']))
      {
        if ($traces[$i]['object'] instanceof $this)
        {
          $result = array($traces[$i]['file'], $traces[$i]['line']);
          break;
        }
      }
    }

    // Remove "~annotated" suffix
    $result[0] = preg_replace('/~annotated$/', '', $result[0]);

    return $result;
  }
}