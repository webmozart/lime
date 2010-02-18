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
 * A glob for loading test files.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeGlob implements LimeLoadable
{
  protected
    $glob          = null,
    $executable    = null,
    $labels        = array();

  /**
   * Constructor.
   *
   * @param string $glob                A valid glob string
   * @param LimeExecutable $executable  The executable used to run the files
   * @param array $labels               The labels of the files
   */
  public function __construct($glob, LimeExecutable $executable, array $labels = array())
  {
    $this->glob = $glob;
    $this->executable = $executable;
    $this->labels = $labels;
  }

  /**
   * (non-PHPdoc)
   * @see LimeLoadable#loadFiles()
   */
  public function loadFiles()
  {
    $files = array();

    foreach (glob($this->glob) as $path)
    {
      $files[] = new LimeFile($path, $this->executable, $this->labels);
    }

    return $files;
  }
}