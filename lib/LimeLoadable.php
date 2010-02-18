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
 * Is able to load test files with their labels and executables.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
interface LimeLoadable
{
  /**
   * Loads and returns the test files.
   *
   * @return array  An array of LimeFile instances
   */
  public function loadFiles();
}