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
 * Factory class for creating LimeInputInterface instances.
 *
 * The available instance names are:
 *
 *   * raw
 *   * tap
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeInputFactory implements LimeInputFactoryInterface
{
  /**
   * (non-PHPdoc)
   * @see output/LimeInputFactoryInterface#create($name)
   */
  public function create($name, LimeOutputInterface $output)
  {
    switch ($name)
    {
      case 'raw':
        return new LimeInputRaw($output);
      case 'tap':
      default:
        return new LimeInputTap($output);
    }
  }
}