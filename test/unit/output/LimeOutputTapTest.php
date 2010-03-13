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

$t = new LimeTest();

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


// @Test: focus() prints the filename only once

  $printer->printLine('# /test/file', LimePrinter::INFO)->once();
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->focus('/test/file');


// @Test: pass() prints and counts passed tests

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printText('ok 1', LimePrinter::OK);
  $printer->printLine(' - A passed test');
  $printer->printText('ok 2', LimePrinter::OK);
  $printer->printLine(' - Another passed test');
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->pass('A passed test', 'Class', 100, '/test/file', 11);
  $output->pass('Another passed test', 'Class', 100, '/test/file', 22);


// @Test: pass() prints no message if none is given

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printLine('ok 1', LimePrinter::OK);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->pass('', 'Class', 100, '/test/file', 11);


// @Test: fail() prints and counts failed tests

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printText('not ok 1', LimePrinter::NOT_OK);
  $printer->printLine(' - A failed test');
  $printer->printLargeBox("Error: A very important error\n(in /test/file on line 11)", LimePrinter::ERROR);
  $printer->printText('not ok 2', LimePrinter::NOT_OK);
  $printer->printLine(' - Another failed test');
  $printer->printLargeBox("Error: A very important error\n(in /test/file on line 11)", LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->fail('A failed test', 'Class', 100, '/test/file', 33, new LimeError('A very important error', '/test/file', 11));
  $output->fail('Another failed test', 'Class', 100, '/test/file', 55, new LimeError('A very important error', '/test/file', 11));


// @Test: fail() prints no message if none is given

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printLine('not ok 1', LimePrinter::NOT_OK);
  $printer->printLargeBox("Error: A very important error\n(in /test/file on line 11)", LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->fail('', 'Class', 100, '/test/file', 11, new LimeError('A very important error', '/test/file', 11));


// @Test: fail() truncates the file path

  // fixtures
  $configuration->reset();
  $configuration->getBaseDir()->returns('/test');
  $configuration->replay();
  $printer->printLine('# /file', LimePrinter::INFO);
  $printer->printLine('not ok 1', LimePrinter::NOT_OK);
  $printer->printLargeBox("Error: A very important error\n(in /file on line 11)", LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->fail('', 'Class', 100, '/test/file', 11, new LimeError('A very important error', '/test/file', 11));


// @Test: skip() prints and counts skipped tests

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printText('ok 1', LimePrinter::SKIP);
  $printer->printText(' - A skipped test ');
  $printer->printLine('# SKIP', LimePrinter::SKIP);
  $printer->printText('ok 2', LimePrinter::SKIP);
  $printer->printText(' - Another skipped test ');
  $printer->printLine('# SKIP', LimePrinter::SKIP);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->skip('A skipped test', 'Class', 100, '/test/file', 11);
  $output->skip('Another skipped test', 'Class', 100, '/test/file', 22);


// @Test: skip() prints no message if none is given

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printText('ok 1', LimePrinter::SKIP);
  $printer->printText(' ');
  $printer->printLine('# SKIP', LimePrinter::SKIP);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->skip('', 'Class', 100, '/test/file', 11);


// @Test: skip() prints the reason for skipping

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printText('ok 1', LimePrinter::SKIP);
  $printer->printText(' ');
  $printer->printLine('# SKIP', LimePrinter::SKIP);
  $printer->printLine('# The reason', LimePrinter::COMMENT);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->skip('', 'Class', 100, '/test/file', 11, 'The reason');


// @Test: todo() prints and counts todos

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printText('not ok 1', LimePrinter::TODO);
  $printer->printText(' - A todo ');
  $printer->printLine('# TODO', LimePrinter::TODO);
  $printer->printText('not ok 2', LimePrinter::TODO);
  $printer->printText(' - Another todo ');
  $printer->printLine('# TODO', LimePrinter::TODO);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->todo('A todo', 'Class', '/test/file', 11);
  $output->todo('Another todo', 'Class', '/test/file', 22);


// @Test: todo() prints no message if none is given

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printText('not ok 1', LimePrinter::TODO);
  $printer->printText(' ');
  $printer->printLine('# TODO', LimePrinter::TODO);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->todo('', 'Class', '/test/file', 11);


// @Test: error() prints an error

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printLargeBox("Error: A very important error\n(in /test/file on line 11)", LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->error(new LimeError('A very important error', '/test/file', 11));


// @Test: error() skips the filename if not set

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printLargeBox("Error: A very important error", LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->error(new LimeError('A very important error', '', ''));


// @Test: error() prints the error traces if available

  // fixtures
  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printLargeBox("MyException: A very important error\n(in /test/file on line 11)", LimePrinter::ERROR);
  $printer->printLine('Exception trace:', LimePrinter::COMMENT);
  $printer->printText('  at ');
  $printer->printText('/test/file', LimePrinter::TRACE);
  $printer->printText(':');
  $printer->printLine(11, LimePrinter::TRACE);
  $printer->printText('  my_function_1() at ');
  $printer->printLine('[internal function]');
  $printer->printText('  my_function_2() at ');
  $printer->printText('file_2', LimePrinter::TRACE);
  $printer->printText(':');
  $printer->printLine(20, LimePrinter::TRACE);
  $printer->printText('  Class3->my_function_3() at ');
  $printer->printLine('[internal function]');
  $printer->printText('  Class4->my_function_4() at ');
  $printer->printText('file_4', LimePrinter::TRACE);
  $printer->printText(':');
  $printer->printLine(40, LimePrinter::TRACE);
  $printer->printLine('');
  $printer->replay();
  // test
  $output->focus('/test/file');
  $trace = array(
    array('function' => 'my_function_1'),
    array('function' => 'my_function_2', 'file' => 'file_2', 'line' => 20),
    array('class' => 'Class3', 'type' => '->', 'function' => 'my_function_3'),
    array('class' => 'Class4', 'type' => '->', 'function' => 'my_function_4', 'file' => 'file_4', 'line' => 40),
  );
  $output->error(new LimeError('A very important error', '/test/file', 11, 'MyException', $trace));


// @Test: error() prints the invocation traces if available

  $printer->printLine('# /test/file', LimePrinter::INFO);
  $printer->printLargeBox("MyException: A very important error\n(in /test/file on line 11)", LimePrinter::ERROR);
  $printer->printLine('Invocation trace:', LimePrinter::COMMENT);
  $printer->printLine('  1) printLine("Foo", 2) was called once');
  $printer->printLine('  2) printLine("Bar") was called never');
  $printer->printLine('');
  $printer->replay();
  // test
  $output->focus('/test/file');
  $trace = array(
    'printLine("Foo", 2) was called once',
    'printLine("Bar") was called never',
  );
  $output->error(new LimeError('A very important error', '/test/file', 11, 'MyException', array(), $trace));


// @Test: error() truncates the file path

  // fixtures
  $configuration->reset();
  $configuration->getBaseDir()->returns('/test');
  $configuration->replay();
  $printer->printLine('# /file', LimePrinter::INFO);
  $printer->printLargeBox("Error: A very important error\n(in /file on line 11)", LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->focus('/test/file');
  $output->error(new LimeError('A very important error', '/test/file', 11));


// @Test: info() prints an information

  // fixtures
  $printer->printLine('# My information', LimePrinter::INFO);
  $printer->replay();
  // test
  $output->info('My information', '/test/file', 11);


// @Test: comment() prints a comment

  // fixtures
  $printer->printLine('# My comment', LimePrinter::COMMENT);
  $printer->replay();
  // test
  $output->comment('My comment', '/test/file', 11);


// @Test: flush() prints the plan and a summary

  // @Test: Case 1 - Correct number of tests

  // fixtures
  $output->focus('/test/file');
  $output->pass('First test', 'Class', 100, '/test/file', 11);
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
  $printer->replay();
  // test
  $output->flush();

  // @Test: Case 2 - Failed tests

  // fixtures
  $output->focus('/test/file');
  $output->pass('First test', 'Class', 100, '/test/file', 11);
  $output->fail('Second test', 'Class', 100, '/test/file', 22, new LimeError('A very important error', '/test/file', 11));
  $output->pass('Third test', 'Class', 100, '/test/file', 33);
  $printer->reset();
  $printer->printLine('1..3');
  $printer->printBox(' Looks like you failed 1 tests of 3.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();

  // @Test: Case 3 - Skipped tests

  // fixtures
  $output->focus('/test/file');
  $output->skip('First test', 'Class', 100, '/test/file', 11);
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
  $printer->replay();
  // test
  $output->flush();

  // @Test: Case 4 - Successful but errors

  // fixtures
  $output->focus('/test/file');
  $output->pass('First test', 'Class', 100, '/test/file', 11);
  $output->error(new LimeError('Some error', '/test/file', 11));
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like some errors occurred.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();

