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
 * Launches test files and passes their output to its own output instance.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeLauncher
{
  protected
    $inputFactory      = null,
    $output             = null,
    $errors             = '',
    $file               = null,
    $process            = null,
    $done               = true,
    $input             = null;

  /**
   * Constructor.
   *
   * @param LimeOutputInterface $output
   * @param array $suppressedMethods
   */
  public function __construct(LimeOutputInterface $output, LimeInputFactoryInterface $inputFactory)
  {
    $this->output = $output;
    $this->inputFactory = $inputFactory;
  }

  /**
   * Launches the given file in a background process.
   *
   * @param string $file
   * @param array $arguments
   */
  public function launch(LimeFile $file)
  {
    $executable = $file->getExecutable();

    $this->file = $file;
    $this->done = false;
    $this->input =  $this->inputFactory->create($executable->getInputName(), $this->output);
    $this->process = new LimeProcess($executable, $file->getPath());
    $this->process->execute();
  }

  /**
   * Returns the file name of the currently launched process.
   *
   * @return string
   */
  public function getCurrentFile()
  {
    return $this->file;
  }

  /**
   * Reads the next chunk of output from the currently launched process.
   */
  public function proceed()
  {
    $data = $this->process->getOutput();

    $this->input->parse($data);

    $this->errors .= $this->process->getErrors();

    while (preg_match('/^(.+)\n/', $this->errors, $matches))
    {
      $this->output->error(new LimeError($matches[1], $this->file->getPath(), 0));
      $this->errors = substr($this->errors, strlen($matches[0]));
    }

    if ($this->process->isClosed())
    {
      if (!$this->input->done())
      {
        // FIXME: Should be handled in a better way
        $buffer = substr($this->input->buffer, 0, strpos($this->input->buffer, "\n"));
        $this->output->error(new LimeError(sprintf('Could not parse test output: "%s"', $buffer), $this->file->getPath(), 1, 'Warning'));
      }

      // if the last error was not followed by \n, it is still in the buffer
      if (!empty($this->errors))
      {
        $this->output->error(new LimeError($this->errors, $this->file->getPath(), 0));
        $this->errors = '';
      }

      $this->done = true;
    }
  }

  /**
   * Returns whether the currently launched process has ended.
   *
   * @return boolean
   */
  public function done()
  {
    return $this->done;
  }
}