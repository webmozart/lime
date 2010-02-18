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

include dirname(__FILE__).'/../bootstrap/unit.php';

LimeAnnotationSupport::enable();

class TestCase extends LimeTestCase
{
  public $impl;

  public function setUp()
  {
    $this->impl->setUp();
  }

  public function tearDown()
  {
    $this->impl->tearDown();
  }

  public function testDoSomething()
  {
    $this->impl->testDoSomething();
  }

  public function testDoSomethingElse()
  {
    $this->impl->testDoSomethingElse();
  }
}


$t = new LimeTest(8);


// @Before

  $output = $t->mock('LimeOutputInterface');
  $configuration = $t->stub('LimeConfiguration');
  $configuration->createTestOutput()->returns($output);
  $configuration->replay();
  $test = new TestCase(null, $configuration);
  $output->reset();
  $test->impl = $t->mock('Test');


// @Test: The methods setUp() and tearDown() are called before and after each test method

  // fixtures
  $test->impl->setUp();
  $test->impl->testDoSomething();
  $test->impl->tearDown();
  $test->impl->setUp();
  $test->impl->testDoSomethingElse();
  $test->impl->tearDown();
  $test->impl->replay();
  // test
  $test->run();
  // assertions
  $test->impl->verify();


// @Test: The method names are converted to comments

  $output->comment('Do something');
  $output->comment('Do something else');
  $output->replay();
  // test
  $test->run();
  // assertions
  $output->verify();
