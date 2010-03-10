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


$t = new LimeTest();



$t->diag('The before callbacks are called before each test method');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $r = new LimeTestRunner();
  $r->addBefore(array($mock, 'setUp'));
  $r->addTest(array($mock, 'testDoSomething'), 'Do something', '/test/file', 11);
  $r->addTest(array($mock, 'testDoSomethingElse'), 'Do something else', '/test/file', 22);
  $mock->setUp('Do something', '/test/file', 11);
  $mock->testDoSomething();
  $mock->setUp('Do something else', '/test/file', 22);
  $mock->testDoSomethingElse();
  $mock->replay();
  // test
  $r->run();


$t->diag('The after callbacks are called before each test method');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $r = new LimeTestRunner();
  $r->addAfter(array($mock, 'tearDown'));
  $r->addTest(array($mock, 'testDoSomething'), '', '', 0);
  $r->addTest(array($mock, 'testDoSomethingElse'), '', '', 0);
  $mock->testDoSomething();
  $mock->tearDown();
  $mock->testDoSomethingElse();
  $mock->tearDown();
  $mock->replay();
  // test
  $r->run();


$t->diag('The before-all callbacks are called before the whole test suite');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $r = new LimeTestRunner();
  $r->addBeforeAll(array($mock, 'setUp'));
  $r->addTest(array($mock, 'testDoSomething'), '', '', 0);
  $r->addTest(array($mock, 'testDoSomethingElse'), '', '', 0);
  $mock->setUp();
  $mock->testDoSomething();
  $mock->testDoSomethingElse();
  $mock->replay();
  // test
  $r->run();


$t->diag('The after-all callbacks are called before the whole test suite');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $r = new LimeTestRunner();
  $r->addAfterAll(array($mock, 'tearDown'));
  $r->addTest(array($mock, 'testDoSomething'), '', '', 0);
  $r->addTest(array($mock, 'testDoSomethingElse'), '', '', 0);
  $mock->testDoSomething();
  $mock->testDoSomethingElse();
  $mock->tearDown();
  $mock->replay();
  // test
  $r->run();


$t->diag('The exception handlers are called when a test throws an exception');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $r = new LimeTestRunner();
  $r->addTest(array($mock, 'testThrowsException'), '', '', 0);
  $r->addExceptionHandler(array($mock, 'handleExceptionFailed'));
  $r->addExceptionHandler(array($mock, 'handleExceptionSuccessful'));
  $mock->testThrowsException()->throws('Exception');
  $mock->method('handleExceptionFailed')->returns(false);
  $mock->method('handleExceptionSuccessful')->returns(true);
  $mock->replay();
  // test
  $r->run();


$t->diag('If no exception handler returns true, the exception is thrown again');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $r = new LimeTestRunner();
  $r->addTest(array($mock, 'testThrowsException'), '', '', 0);
  $r->addExceptionHandler(array($mock, 'handleExceptionFailed'));
  $mock->testThrowsException()->throws('Exception');
  $mock->method('handleExceptionFailed')->returns(false);
  $mock->replay();
  // test
  $t->expect('Exception');
  try
  {
    $r->run();
    $t->fail('The exception was thrown');
  }
  catch (Exception $e)
  {
  }
