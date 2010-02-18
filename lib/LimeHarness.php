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

class LimeHarness
{
  protected
    $configuration    = null,
    $output           = null;

  /**
   * Constructor.
   *
   * @param array $options  The options
   */
  public function __construct(LimeConfiguration $configuration, LimeLoader $loader, array $options = array())
  {
    $this->configuration = $configuration;

    $output = $configuration->createSuiteOutput();

    if ($configuration->getProcesses() > 1 && !$output->supportsThreading())
    {
      throw new LogicException(sprintf('The output "%s" does not support multi-processing', $type));
    }

    if ($output instanceof LimeOutputSuite)
    {
      $output->setLoader($loader);
    }

    $this->output = new LimeOutputProxy($output);
  }

  public function run(array $files)
  {
    reset($files);

    $launchers = array();

    for ($i = 0; $i < $this->configuration->getProcesses(); ++$i)
    {
      $launchers[] = new LimeLauncher($this->output, $this->configuration->getParserFactory());
    }

    do
    {
      $done = true;

      foreach ($launchers as $launcher)
      {
        if ($launcher->done() && !is_null(key($files)))
        {
          // start and close the file explicitly in case the file contains syntax errors
          $this->output->focus(current($files)->getPath());
          $launcher->launch(current($files));

          next($files);
        }

        if (!$launcher->done())
        {
          $this->output->focus($launcher->getCurrentFile()->getPath());

          $launcher->proceed();
          $done = false;

          if ($launcher->done())
          {
            // start and close the file explicitly in case the file contains syntax errors
            $this->output->close();
          }
        }
      }
    }
    while (!$done);

    $this->output->flush();

    return !$this->output->getResult()->isFailed();
  }
}