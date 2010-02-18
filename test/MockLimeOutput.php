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
 * Mimics the behaviour of LimeTest for testing.
 *
 * The public properties $fails and $passes give you information about how
 * often a fail/pass was reported to this test instance.
 *
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 * @version    SVN: $Id: MockLimeOutput.php 23701 2009-11-08 21:23:40Z bschussek $
 */
class MockLimeOutput extends LimeOutputNone
{
  /**
   * The number of reported failing tests
   * @var integer
   */
  public $fails = 0;

  /**
   * The number of reported passing tests
   * @var integer
   */
  public $passes = 0;

  public function pass($message, $file, $line)
  {
    ++$this->passes;
  }

  public function fail($message, $file, $line, $error = null)
  {
    ++$this->fails;
  }
}
