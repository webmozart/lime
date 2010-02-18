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
 * Scans the file systems for test file.
 *
 * All files registered in the configuration, that is passed to the constructor,
 * will be loaded. The file paths can then be accessed using the different
 * getFile*() methods.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeLoader
{
  private
    $files          = array(),
    $labels         = array(),
    $filesByName    = array(),
    $configuration  = null;

  /**
   * Loads all tests registered in the given configuration.
   *
   * @param LimeConfiguration $configuration
   */
  public function __construct(LimeConfiguration $configuration)
  {
    $this->configuration = $configuration;

    foreach ($configuration->getLoadables() as $loadable)
    {
      $this->load($loadable);
    }
  }

  /**
   * Registers a test file path in the test suite.
   *
   * @param string $path
   * @param array $labels
   */
  protected function load(LimeLoadable $loadable)
  {
    foreach ($loadable->loadFiles() as $file)
    {
      $path = $file->getPath();
      $name = basename($path, $this->configuration->getSuffix());

      if (!isset($this->files[$path]))
      {
        $this->files[$path] = $file;

        if (!isset($this->filesByName[$name]))
        {
          $this->filesByName[$name] = array();
        }

        // allow multiple files with the same name
        $this->filesByName[$name][] = $file;
      }
      else
      {
        // merge labels into existing files
        $this->files[$path]->addLabels($file->getLabels());
      }

      foreach ($file->getLabels() as $label)
      {
        if (!isset($this->labels[$label]))
        {
          $this->labels[$label] = new LimeLabel();
        }

        $this->labels[$label]->addFile($this->files[$path]);
      }
    }
  }

  /**
   * Returns whether the given label exists.
   *
   * @param  string $label
   * @return boolean
   */
  public function isLabel($label)
  {
    preg_match('/^[+-]?(.+)$/', $label, $matches);

    return isset($this->labels[$matches[1]]);
  }

  /**
   * Returns all files with the given labels.
   *
   * @param  array $labels
   * @return array
   */
  public function getFilesByLabels(array $labels = array())
  {
    $time = microtime();
    $result = new LimeLabel();

    foreach ($this->files as $file)
    {
      $result->addFile($file);
    }

    if (count($labels) > 0)
    {
      foreach ($labels as $label)
      {
        if (!preg_match('/^([-+]?)(.+)$/', $label, $matches))
        {
          throw new InvalidArgumentException(sprintf('Invalid label format: "%s"', $label));
        }

        $operation = $matches[1];
        $label = $matches[2];

        if (!isset($this->labels[$label]))
        {
          throw new InvalidArgumentException(sprintf('Unknown label: "%s"', $label));
        }

        if ($operation == '+')
        {
          $result = $result->add($this->labels[$label]);
        }
        else if ($operation == '-')
        {
          $result = $result->subtract($this->labels[$label]);
        }
        else
        {
          $result = $result->intersect($this->labels[$label]);
        }
      }
    }

    return $result->getFiles();
  }

  /**
   * Returns all files with the given name.
   *
   * @param  string $name
   * @return array
   */
  public function getFilesByName($name)
  {
    if (!isset($this->filesByName[$name]))
    {
      throw new InvalidArgumentException(sprintf('Unknown test: "%s"', $name));
    }

    return $this->filesByName[$name];
  }

  /**
   * Returns the file with the given path.
   *
   * @param  string $path
   * @return LimeFile
   */
  public function getFileByPath($path)
  {
    if (!isset($this->files[$path]))
    {
      throw new InvalidArgumentExceptoin(sprintf('Unknown file: "%s"', $path));
    }

    return $this->files[$path];
  }
}