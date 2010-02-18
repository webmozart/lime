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

class LimeOutputProxy extends LimeOutput
{
  private
    $output       = null;

  public function __construct(LimeOutputInterface $output = null)
  {
    $this->output = is_null($output) ? new LimeOutputNone() : $output;
  }

  public function supportsThreading()
  {
    return $this->output->supportsThreading();
  }

  public function focus($file)
  {
    parent::focus($file);

    $this->output->focus($file);
  }

  public function close()
  {
    $this->output->close();
  }

  public function plan($amount)
  {
    parent::plan($amount);

    $this->output->plan($amount);
  }

  public function pass($message, $file, $line)
  {
    parent::pass($message, $file, $line);

    $this->output->pass($message, $file, $line);
  }

  public function fail($message, $file, $line, $error = null)
  {
    parent::fail($message, $file, $line, $error);

    $this->output->fail($message, $file, $line, $error);
  }

  public function skip($message, $file, $line)
  {
    parent::skip($message, $file, $line);

    $this->output->skip($message, $file, $line);
  }

  public function todo($message, $file, $line)
  {
    parent::todo($message, $file, $line);

    $this->output->todo($message, $file, $line);
  }

  public function warning($message, $file, $line)
  {
    parent::warning($message, $file, $line);

    $this->output->warning($message, $file, $line);
  }

  public function error(LimeError $error)
  {
    parent::error($error);

    $this->output->error($error);
  }

  public function comment($message)
  {
    $this->output->comment($message);
  }

  public function flush()
  {
    $this->output->flush();
  }

  public function getDubiousFiles()
  {
    $files = array();

    foreach ($this as $file => $logic)
    {
      if (!$logic->isSuccessful() || $logic->getWarnings())
      {
        $files[] = $file;
      }
    }

    return $files;
  }
}