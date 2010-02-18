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
 * A single file in a test suite.
 *
 * The file may be assigned to labels.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeFile implements LimeLoadable
{
  private
    $path          = null,
    $executable    = null,
    $labels        = array();

  /**
   * Constructor.
   *
   * @param string $path  The path to the file
   */
  public function __construct($path, LimeExecutable $executable, array $labels = array())
  {
    if (!is_file($path))
    {
      throw new InvalidArgumentException(sprintf('The file "%s" does not exist', $path));
    }

    $this->path = realpath($path);
    $this->executable = $executable;
    $this->labels = $labels;
  }

  /**
   * Returns the path to the file.
   *
   * @return string
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * Returns the executable for the file.
   *
   * @return LimeExecutable
   */
  public function getExecutable()
  {
    return $this->executable;
  }

  /**
   * Adds the given labels to the file.
   *
   * @param array $labels
   */
  public function addLabels(array $labels)
  {
    $this->labels = array_merge($this->labels, $labels);
  }

  /**
   * Returns the labels of this file.
   *
   * @return array
   */
  public function getLabels()
  {
    return array_values(array_unique($this->labels));
  }

  /**
   * @see LimeLoadable#loadFiles()
   */
  public function loadFiles()
  {
    return array($this);
  }
}