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

class LimeTestCase extends LimeTest
{
  protected
    $testRunner   = null;

  public function __construct(LimeConfiguration $configuration = null)
  {
    parent::__construct($configuration);

    $this->testRunner = new LimeTestRunner($this->getOutput());
    $this->testRunner->addBefore(array($this, 'beginTest'));
    $this->testRunner->addBefore(array($this, 'setUp'));
    $this->testRunner->addAfter(array($this, 'tearDown'));

    // attention: the following lines are not tested
    $this->testRunner->addExceptionHandler(array($this, 'handleException'));
    $this->testRunner->addAfter(array($this, 'endTest'));

    $class = new ReflectionClass($this);

    foreach ($class->getMethods() as $method)
    {
      if (strpos($method->getName(), 'test') === 0 && strlen($method->getName()) > 4)
      {
        $this->testRunner->addTest(array($this, $method->getName()), $this->humanize($method->getName()), $method->getFileName(), $method->getStartLine());
      }
    }
  }

  public function setUp() {}

  public function tearDown() {}

  public function run()
  {
    $this->testRunner->run();
  }

  protected function humanize($method)
  {
    if (substr($method, 0, 4) == 'test')
    {
      $method = substr($method, 4);
    }

    $method = preg_replace('/([a-z])([A-Z])/', '$1 $2', $method);

    return ucfirst(strtolower($method));
  }
}