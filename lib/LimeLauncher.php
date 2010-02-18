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
    $parserFactory      = null,
    $output             = null,
    $errors             = '',
    $file               = null,
    $process            = null,
    $done               = true,
    $parser             = null;

  /**
   * Constructor.
   *
   * @param LimeOutputInterface $output
   * @param array $suppressedMethods
   */
  public function __construct(LimeOutputInterface $output, LimeParserFactoryInterface $parserFactory)
  {
    $this->output = $output;
    $this->parserFactory = $parserFactory;
  }

  /**
   * Launches the given file in a background process.
   *
   * @param string $file
   * @param array $arguments
   */
  public function launch(LimeFile $file, array $arguments = array())
  {
    $executable = $file->getExecutable();

    $this->file = $file;
    $this->done = false;
    $this->parser =  $this->parserFactory->create($executable->getParserName(), $this->output);
    $this->process = new LimeProcess($file->getPath(), $executable, $arguments);
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

    $this->parser->parse($data);

    $this->errors .= $this->process->getErrors();

    while (preg_match('/^(.+)\n/', $this->errors, $matches))
    {
      $this->output->warning($matches[1], $this->file->getPath(), 0);
      $this->errors = substr($this->errors, strlen($matches[0]));
    }

    if ($this->process->isClosed())
    {
      if (!$this->parser->done())
      {
        // FIXME: Should be handled in a better way
        $buffer = substr($this->parser->buffer, 0, strpos($this->parser->buffer, "\n"));
        $this->output->warning(sprintf('Could not parse test output: "%s"', $buffer), $this->file->getPath(), 1);
      }

      // if the last error was not followed by \n, it is still in the buffer
      if (!empty($this->errors))
      {
        $this->output->warning($this->errors, $this->file->getPath(), 0);
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