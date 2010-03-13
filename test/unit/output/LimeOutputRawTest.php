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

LimeAnnotationSupport::enable();

$t = new LimeTest();


// @Before

  $output = new LimeOutputRaw();
  ob_start();
  $output->focus('/test/file'); // do something to avoid the header being printed every test
  ob_end_clean();


// @After

  $output = null;


// @Test: focus() prints the method call as serialized array

  // test
  ob_start();
  $output->focus('/test/file');
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('focus', array('/test/file')))."\n", 'The method call is serialized');


// @Test: close() prints the method call as serialized array

  // test
  ob_start();
  $output->close();
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('close', array()))."\n", 'The method call is serialized');


// @Test: pass() prints the method call as serialized array

  // test
  ob_start();
  $output->pass('A passed test', 'Class', 100, '/test/file', 11);
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('pass', array('A passed test', 'Class', 100, '/test/file', 11)))."\n", 'The method call is serialized');


// @Test: fail() prints the method call as serialized array

  // test
  ob_start();
  $output->fail('A failed test', 'Class', 100, '/test/file', 11, new LimeError('A very important error', '/test/file', 11));
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('fail', array('A failed test', 'Class', 100, '/test/file', 11, new LimeError('A very important error', '/test/file', 11))))."\n", 'The method call is serialized');


// @Test: skip() prints the method call as serialized array

  // test
  ob_start();
  $output->skip('A skipped test', 'Class', 100, '/test/file', 11, 'Reason');
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('skip', array('A skipped test', 'Class', 100, '/test/file', 11, 'Reason')))."\n", 'The method call is serialized');


// @Test: todo() prints the method call as serialized array

  // test
  ob_start();
  $output->todo('A todo', 'Class', '/test/file', 11);
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('todo', array('A todo', 'Class', '/test/file', 11)))."\n", 'The method call is serialized');


// @Test: error() prints the method call as serialized array

  // test
  ob_start();
  $output->error($error = new LimeError('An error', '/test/file', 11));
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('error', array($error)))."\n", 'The method call is serialized');


// @Test: comment() prints the method call as serialized array

  // test
  ob_start();
  $output->comment('A comment');
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('comment', array('A comment')))."\n", 'The method call is serialized');


// @Test: flush() prints the method call as serialized array

  // test
  ob_start();
  $output->flush();
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('flush', array()))."\n", 'The method call is serialized');


// @Test: Strings in arguments are escaped

  // test
  ob_start();
  $output->comment("A \\n\\r comment \n with line \r breaks");
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('comment', array('A \\n\\r comment \n with line \r breaks')))."\n", 'LFs and CRs are escaped');