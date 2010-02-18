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

$t = new LimeTest(73);

// @Before

  $printer = $t->mock('LimePrinter', array('strict' => true));
  $configuration = $t->stub('LimeConfiguration');
  $configuration->replay();
  $output = new LimeOutputTap($printer, $configuration);


// @After

  $printer = null;
  $output = null;


// @Test: focus() prints the filename

  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->replay();
  // test
  $output->focus('/test/file');
  // assertions
  $printer->verify();


// @Test: focus() prints the filename only once

  $printer->printLine('# /test/file', LimePrinter::INFO)->once();
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->focus('/test/file');
  // assertions
  $printer->verify();


// @Test: pass() prints and counts passed tests

  // fixtures
  $printer->printText('ok 1', LimePrinter::OK);
  $printer->printLine(' - A passed test');
  $printer->printText('ok 2', LimePrinter::OK);
  $printer->printLine(' - Another passed test');
  $printer->replay();
  // test
  $output->pass('A passed test', '/test/file', 11);
  $output->pass('Another passed test', '/test/file', 22);
  // assertions
  $printer->verify();


// @Test: pass() prints no message if none is given

  // fixtures
  $printer->printLine('ok 1', LimePrinter::OK);
  $printer->replay();
  // test
  $output->pass('', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: fail() prints and counts failed tests

  // fixtures
  $printer->printText('not ok 1', LimePrinter::NOT_OK);
  $printer->printLine(' - A failed test');
  $printer->printLine('#     Failed test (/test/file at line 33)', LimePrinter::COMMENT);
  $printer->printText('not ok 2', LimePrinter::NOT_OK);
  $printer->printLine(' - Another failed test');
  $printer->printLine('#     Failed test (/test/file at line 55)', LimePrinter::COMMENT);
  $printer->printLine('#       error', LimePrinter::COMMENT);
  $printer->printLine('#       message', LimePrinter::COMMENT);
  $printer->replay();
  // test
  $output->fail('A failed test', '/test/file', 33);
  $output->fail('Another failed test', '/test/file', 55, "error\nmessage");
  // assertions
  $printer->verify();


// @Test: fail() prints no message if none is given

  // fixtures
  $printer->printLine('not ok 1', LimePrinter::NOT_OK);
  $printer->printLine('#     Failed test (/test/file at line 11)', LimePrinter::COMMENT);
  $printer->replay();
  // test
  $output->fail('', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: fail() truncates the file path

  // fixtures
  $configuration->reset();
  $configuration->getBaseDir()->returns('/test');
  $configuration->replay();
  $printer->printLine('not ok 1', LimePrinter::NOT_OK);
  $printer->printLine('#     Failed test (/file at line 11)', LimePrinter::COMMENT);
  $printer->replay();
  // test
  $output->fail('', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: skip() prints and counts skipped tests

  // fixtures
  $printer->printText('ok 1', LimePrinter::SKIP);
  $printer->printText(' - A skipped test ');
  $printer->printLine('# SKIP', LimePrinter::SKIP);
  $printer->printText('ok 2', LimePrinter::SKIP);
  $printer->printText(' - Another skipped test ');
  $printer->printLine('# SKIP', LimePrinter::SKIP);
  $printer->replay();
  // test
  $output->skip('A skipped test', '/test/file', 11);
  $output->skip('Another skipped test', '/test/file', 22);
  // assertions
  $printer->verify();


// @Test: skip() prints no message if none is given

  // fixtures
  $printer->printText('ok 1', LimePrinter::SKIP);
  $printer->printText(' ');
  $printer->printLine('# SKIP', LimePrinter::SKIP);
  $printer->replay();
  // test
  $output->skip('', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: todo() prints and counts todos

  // fixtures
  $printer->printText('not ok 1', LimePrinter::TODO);
  $printer->printText(' - A todo ');
  $printer->printLine('# TODO', LimePrinter::TODO);
  $printer->printText('not ok 2', LimePrinter::TODO);
  $printer->printText(' - Another todo ');
  $printer->printLine('# TODO', LimePrinter::TODO);
  $printer->replay();
  // test
  $output->todo('A todo', '/test/file', 11);
  $output->todo('Another todo', '/test/file', 22);
  // assertions
  $printer->verify();


// @Test: todo() prints no message if none is given

  // fixtures
  $printer->printText('not ok 1', LimePrinter::TODO);
  $printer->printText(' ');
  $printer->printLine('# TODO', LimePrinter::TODO);
  $printer->replay();
  // test
  $output->todo('', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: warning() prints a warning

  // fixtures
  $printer->printLargeBox("A very important warning\n(in /test/file on line 11)", LimePrinter::WARNING);
  $printer->replay();
  // test
  $output->warning('A very important warning', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: warning() truncates the file path

  // fixtures
  $configuration->reset();
  $configuration->getBaseDir()->returns('/test');
  $configuration->replay();
  $printer->printLargeBox("A very important warning\n(in /file on line 11)", LimePrinter::WARNING);
  $printer->replay();
  // test
  $output->warning('A very important warning', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: error() prints an error

  // fixtures
  $printer->printLargeBox("Error: A very important error\n(in /test/file on line 11)", LimePrinter::ERROR);
  $printer->printLine('Exception trace:', LimePrinter::COMMENT);
  $printer->method('printText')->atLeastOnce();
  $printer->method('printLine')->atLeastOnce();
  $printer->replay();
  // test
  $output->error(new LimeError('A very important error', '/test/file', 11));
  // assertions
  $printer->verify();


// @Test: error() truncates the file path

  // fixtures
  $configuration->reset();
  $configuration->getBaseDir()->returns('/test');
  $configuration->replay();
  $printer->printLargeBox("Error: A very important error\n(in /file on line 11)", LimePrinter::ERROR);
  $printer->printLine('Exception trace:', LimePrinter::COMMENT);
  $printer->method('printText')->atLeastOnce();
  $printer->method('printLine')->atLeastOnce();
  $printer->replay();
  // test
  $output->error(new LimeError('A very important error', '/test/file', 11));
  // assertions
  $printer->verify();


// @Test: info() prints an information

  // fixtures
  $printer->printLine('# My information', LimePrinter::INFO);
  $printer->replay();
  // test
  $output->info('My information', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: comment() prints a comment

  // fixtures
  $printer->printLine('# My comment', LimePrinter::COMMENT);
  $printer->replay();
  // test
  $output->comment('My comment', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: flush() prints the plan and a summary

  // @Test: Case 1 - Too many tests

  // fixtures
  $output->plan(1);
  $output->pass('First test', '/test/file', 11);
  $output->pass('Second test', '/test/file', 22);
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like you only planned 1 tests but ran 2.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 2 - Too many tests including failed tests

  // fixtures
  $output->plan(1);
  $output->pass('First test', '/test/file', 11);
  $output->fail('Second test', '/test/file', 22);
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like you failed 1 tests of 2.', LimePrinter::ERROR);
  $printer->printBox(' Looks like you only planned 1 tests but ran 2.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 3 - Too few tests

  // fixtures
  $output->plan(2, '/test/file');
  $output->pass('First test', '/test/file', 11);
  $printer->reset();
  $printer->printLine('1..2');
  $printer->printBox(' Looks like you planned 2 tests but only ran 1.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 4 - Correct number of tests

  // fixtures
  $output->plan(1);
  $output->pass('First test', '/test/file', 11);
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 5 - Failed tests

  // fixtures
  $output->plan(3, '/test/file');
  $output->pass('First test', '/test/file', 11);
  $output->fail('Second test', '/test/file', 22);
  $output->pass('Third test', '/test/file', 33);
  $printer->reset();
  $printer->printLine('1..3');
  $printer->printBox(' Looks like you failed 1 tests of 3.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 6 - Failed and too few tests

  // fixtures
  $output->plan(3, '/test/file');
  $output->pass('First test', '/test/file', 11);
  $output->fail('Second test', '/test/file', 22);
  $printer->reset();
  $printer->printLine('1..3');
  $printer->printBox(' Looks like you failed 1 tests of 2.', LimePrinter::ERROR);
  $printer->printBox(' Looks like you planned 3 tests but only ran 2.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 7 - No plan

  // fixtures
  $output->pass('First test', '/test/file', 11);
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 8 - Skipped tests

  // fixtures
  $output->plan(1);
  $output->skip('First test', '/test/file', 11);
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 9 - Successful but warnings

  // fixtures
  $output->plan(1);
  $output->pass('First test', '/test/file', 11);
  $output->warning('Some warning', '/test/file', 11);
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like you\'re nearly there.', LimePrinter::WARNING);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 9 - Successful but errors

  // fixtures
  $output->plan(1);
  $output->pass('First test', '/test/file', 11);
  $output->error(new LimeError('Some error', '/test/file', 11));
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like some errors occurred.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 10 - Several plans

  // fixtures
  $output->plan(1);
  $output->pass('First test', '/test/file', 11);
  $output->plan(1);
  $output->pass('Second test', '/test/file', 11);
  $printer->reset();
  $printer->printLine('1..2');
  $printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();