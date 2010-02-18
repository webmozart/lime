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
 * Factory class for creating output instances.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
interface LimeOutputFactoryInterface
{
  /**
   * Creates a new LimeOutputInterface instance for the given name.
   *
   * Names can be defined by the concrete factory implementation.
   *
   * @param string $name
   */
  public function create($name);
}