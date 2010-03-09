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
 * Stores an error and optionally its trace.
 *
 * This class is similar to PHP's native Exception class, but is guaranteed
 * to be serializable. The native Exception class is not serializable if the
 * traces contain circular references between objects.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 * @version    SVN: $Id: LimeError.php 23701 2009-11-08 21:23:40Z bschussek $
 */
class LimeError implements Serializable
{
  private
    $type              = null,
    $message           = null,
    $file              = null,
    $line              = null,
    $trace             = null,
    $invocationTrace   = null;

  /**
   * Creates a new instance and copies the data from an exception.
   *
   * @param  Exception $exception
   * @return LimeError
   */
  public static function fromException(Exception $exception)
  {
    $file = $exception->getFile();
    $line = $exception->getLine();
    $trace = $exception->getTrace();
    $invocationTrace = array();

    // Remove all the parts from the trace that have been generated inside
    // the mock object. In the end, only the traces that led to the erroneous
    // method call remain there.
    // It would be nice if we could do that inside LimeMockException, but we
    // can't because getTrace() is final.
    if ($exception instanceof LimeMockException)
    {
      $invocationTrace = iterator_to_array($exception->getInvocationTrace());

      $class = get_class($exception->getMock());

      while (count($trace) > 0 && isset($trace[0]['class']) && $trace[0]['class'] == $class)
      {
        $file = isset($trace[0]['file']) ? $trace[0]['file'] : null;
        $line = isset($trace[0]['line']) ? $trace[0]['line'] : null;

        array_shift($trace);
      }
    }

    // Remove all the parts from the trace that have been generated in the
    // annotation support, the CLI etc. They are irrelevant for the testing
    // developer.
    for ($i = 0, $c = count($trace); $i < $c; ++$i)
    {
      if (strpos($trace[$i]['function'], '__lime_annotation_') === 0)
      {
        for (; $i < $c; ++$i)
        {
          unset($trace[$i]);
        }
      }
    }

    return new self($exception->getMessage(), $file, $line, get_class($exception), $trace, $invocationTrace);
  }

  /**
   * Constructor.
   *
   * @param string  $message  The error message
   * @param string  $file     The file where the error occurred
   * @param integer $line     The line where the error occurred
   * @param string  $type     The error type, f.i. "Fatal Error"
   * @param array   $trace    The traces of the error
   */
  public function __construct($message, $file, $line, $type = 'Error', array $trace = array(), array $invocationTrace = array())
  {
    $this->message = $message;
    $this->file = $file;
    $this->line = $line;
    $this->type = $type;
    $this->trace = $trace;
    $this->invocationTrace = $invocationTrace;
  }

  /**
   * Returns the error type.
   *
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Returns the error message.
   *
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Returns the file where the error occurred.
   *
   * @return string
   */
  public function getFile()
  {
    return $this->file;
  }

  /**
   * Returns the line where the error occurred.
   *
   * @return integer
   */
  public function getLine()
  {
    return $this->line;
  }

  /**
   * Returns the trace of the error.
   *
   * @return array
   */
  public function getTrace()
  {
    return $this->trace;
  }

  /**
   * Returns the invocation trace of mock errors.
   *
   * @return array
   */
  public function getInvocationTrace()
  {
    return $this->invocationTrace;
  }

  /**
   * Serializes the error.
   *
   * @see    Serializable#serialize()
   * @return string   The serialized error content
   */
  public function serialize()
  {
    $traces = $this->trace;

    foreach ($traces as &$trace)
    {
      if (array_key_exists('args', $trace))
      {
        foreach ($trace['args'] as &$value)
        {
          // TODO: This should be improved. Maybe we can check for recursions
          // and only exclude duplicate objects from the trace
          if (is_object($value))
          {
            // replace object by class name
            $value = sprintf('object (%s) (...)', get_class($value));
          }
          else if (is_array($value))
          {
            $value = 'array(...)';
          }
        }
      }
    }

    return serialize(array($this->file, $this->line, $this->message, $traces, $this->type, $this->invocationTrace));
  }

  /**
   * Unserializes an error.
   *
   * @see   Serializable#unserialize()
   * @param string $data  The serialized error content
   */
  public function unserialize($data)
  {
    list($this->file, $this->line, $this->message, $this->trace, $this->type, $this->invocationTrace) = unserialize($data);
  }
}