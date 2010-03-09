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

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(10);


// @Before

  $output = new LimeOutputLogicProxy();


// @After

  $output = null;


// @Test: Method calls are forwarded to a decorated object

  // fixtures
  $mock = $t->mock('LimeOutputInterface');
  $mock->focus('/test/file');
  $mock->pass('A passed test', '/test/script', 11);
  $mock->fail('A failed test', '/test/script', 11, 'The error');
  $mock->skip('A skipped test', '/test/script', 11);
  $mock->todo('A todo', '/test/script', 11);
  $mock->warning('A warning', '/test/script', 11);
  $mock->error(new LimeError('An error', '/test/script', 11));
  $mock->comment('A comment');
  $mock->flush();
  $mock->replay();
  $output = new LimeOutputLogicProxy($mock);
  // test
  $output->focus('/test/file');
  $output->pass('A passed test', '/test/script', 11);
  $output->fail('A failed test', '/test/script', 11, 'The error');
  $output->skip('A skipped test', '/test/script', 11);
  $output->todo('A todo', '/test/script', 11);
  $output->warning('A warning', '/test/script', 11);
  $output->error(new LimeError('An error', '/test/script', 11));
  $output->comment('A comment');
  $output->flush();
  // assertions
  $mock->verify();


// @Test: getDubiousFiles() returns the scripts that contained failures, warnings or errors

  // test
  $output->focus('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->focus('/test/fail');
  $output->fail('A failed test', '/test/fail', 11);
  $output->focus('/test/warning');
  $output->warning('A warning', '/test/warning', 11);
  $output->focus('/test/error');
  $output->error(new LimeError('An error', '/test/error', 11));
  // assertions
  $actual = $output->getDubiousFiles();
  $expected = array('/test/fail', '/test/warning', '/test/error');
  $t->is($actual, $expected, 'The correct test files are returned');