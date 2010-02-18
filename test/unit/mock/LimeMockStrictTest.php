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

include dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../MockLimeOutput.php';

LimeAnnotationSupport::enable();


$t = new LimeTest(17);


// @Before

  $output = new MockLimeOutput();
  $m = LimeMock::create('TestClass', $output, array('strict' => true));


// @After

  $output = null;
  $m = null;


// @Test: ->verify() passes if methods were called in the correct order

  // test
  $m->testMethod1();
  $m->testMethod2('Foobar');
  $m->replay();
  $m->testMethod1();
  $m->testMethod2('Foobar');
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: ->verify() passes if a method is expected both with any and with concrete parameters

  // test
  $m->method('testMethod')->once();
  $m->testMethod(1, 'foobar');
  $m->replay();
  $m->testMethod('ramble on');
  $m->testMethod(1, 'foobar');
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: An exception is thrown if methods are called in the wrong order

  // fixtures
  $m->method1();
  $m->method2();
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->method2();


// @Test: An exception is thrown if too many methods are called

  // test
  $m->method1();
  $m->replay();
  $m->method1();
  $t->expect('LimeMockException');
  $m->method2();


// @Test: If the option "no_exceptions" is set, ->verify() fails if methods were called in the wrong order

  // test
  $m = LimeMock::create('TestClass', $output, array('strict' => true, 'no_exceptions' => true));
  $m->method1();
  $m->method2();
  $m->method3();
  $m->replay();
  $m->method3();
  $m->method1();
  $m->method2();
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 1, 'One test failed');


// @Test: The order of the tests remains intact when using times()

  // @Test: Case 1 - Assertion fails

  // fixtures
  $m->method1()->times(3);
  $m->method2();
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->method1();
  $m->method1();
  $m->method2();

  // @Test: Case 2 - Assertion succeeds

  // fixtures
  $m->method1()->times(3);
  $m->method2();
  $m->replay();
  // test
  $m->method1();
  $m->method1();
  $m->method1();
  $m->method2();
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: The order of the tests remains intact when using atLeastOnce()

  // @Test: Case 1 - Assertion fails

  // fixtures
  $m->method1()->atLeastOnce();
  $m->method2();
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->method2();

  // @Test: Case 2 - Assertion succeeds

  // fixtures
  $m->method1()->atLeastOnce();
  $m->method2();
  $m->replay();
  // test
  $m->method1();
  $m->method1();
  $m->method1();
  $m->method2();
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: Parameters are compared using strict comparison by default

  // @Test: - Case 1: Type comparison fails

  // fixture
  $m->testMethod(1);
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->testMethod('1');


  // @Test: - Case 2: Type comparison passes

  // test
  $m->testMethod(1);
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');

