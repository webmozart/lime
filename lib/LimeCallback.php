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
 * A callback that loads test files.
 *
 * The callback receives the executable and the labels passed to the constructor
 * of this class as first two arguments.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeCallback implements LimeLoadable
{
  protected
    $callback      = null,
    $executable    = null,
    $labels        = array();

  /**
   * Constructor.
   *
   * @param callable $callback          The callback which should return an
   *                                    array of LimeFile instances
   * @param LimeExecutable $executable  The executable that is passed to the
   *                                    callable
   * @param array $labels               The lables that are passed to the
   *                                    callable
   */
  public function __construct($callback, LimeExecutable $executable, array $labels = array())
  {
    $this->callback = $callback;
    $this->executable = $executable;
    $this->labels = $labels;
  }

  /**
   * (non-PHPdoc)
   * @see LimeLoadable#loadFiles()
   */
  public function loadFiles()
  {
    return call_user_func($this->callback, $this->executable, $this->labels);
  }
}