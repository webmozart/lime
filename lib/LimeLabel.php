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
 * A label for test files.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeLabel
{
  private
    $files         = array();

  /**
   * Adds the file with the given path to the label.
   *
   * @param string $path
   */
  public function addFile(LimeFile $file)
  {
    $this->files[$file->getPath()] = $file;
  }

  /**
   * Returns all files in this label.
   *
   * @return array
   */
  public function getFiles()
  {
    return array_values($this->files);
  }

  /**
   * Returns the intersection of this and the given label.
   *
   * The returned label contains all test files that are present in this AND
   * the other label.
   *
   * @param  LimeLabel $label
   * @return LimeLabel
   */
  public function intersect(LimeLabel $label)
  {
    $result = new LimeLabel();
    $result->files = array_intersect_key($this->files, $label->files);

    return $result;
  }

  /**
   * Returns the sum of this and the given label.
   *
   * The returned label contains all test files that are present in this OR
   * the other label.
   *
   * @param  LimeLabel $label
   * @return LimeLabel
   */
  public function add(LimeLabel $label)
  {
    $result = new LimeLabel();
    $result->files = array_merge($this->files, $label->files);

    return $result;
  }

  /**
   * Returns the difference of this and the given label.
   *
   * The returned label contains all test files that are present in this
   * BUT NOT the other label.
   *
   * @param  LimeLabel $label
   * @return LimeLabel
   */
  public function subtract(LimeLabel $label)
  {
    $result = new LimeLabel();
    $result->files = array_diff_key($this->files, $label->files);

    return $result;
  }
}