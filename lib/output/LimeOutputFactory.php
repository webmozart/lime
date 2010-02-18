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
 * Factory class for creating LimeOutputInterface instances.
 *
 * The available instance names are:
 *
 *   * raw
 *   * xml
 *   * array
 *   * suite
 *   * tap
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeOutputFactory implements LimeOutputFactoryInterface
{
  protected
    $configuration    = null;

  /**
   * Constructor.
   *
   * @param LimeConfiguration $configuration
   */
  public function __construct(LimeConfiguration $configuration)
  {
    $this->configuration = $configuration;
  }

  /**
   * (non-PHPdoc)
   * @see output/LimeOutputFactoryInterface#create($name)
   */
  public function create($name)
  {
    $colorizer = LimeColorizer::isSupported() || $this->configuration->getForceColors() ? new LimeColorizer() : null;
    $printer = new LimePrinter($colorizer);

    switch ($name)
    {
      case 'raw':
        return new LimeOutputRaw();
      case 'xml':
        return new LimeOutputXml();
      case 'array':
        return new LimeOutputArray($this->configuration->getSerialize());
      case 'suite':
        return new LimeOutputSuite($printer, $this->configuration);
      case 'tap':
      default:
        return new LimeOutputTap($printer, $this->configuration);
    }
  }
}