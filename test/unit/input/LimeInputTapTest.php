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

  $executable = new LimeExecutable(LimeExecutable::php().' %file%');
  $file = tempnam(sys_get_temp_dir(), 'lime');
  $output = $t->mock('LimeOutputInterface');
  $input = new LimeInputTap($output);


// @After

  $file = null;
  $output = null;
  $input = null;


// @Test: Successful tests are passed to pass()

  // fixtures
  $output->pass('A passed test', '', 0, '', '');
  $output->replay();
  // test
  $input->parse("ok 1 - A passed test\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Successful tests without message are passed to pass()

  // fixtures
  $output->pass('', '', 0, '', '');
  $output->replay();
  // test
  $input->parse("ok 1\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Failed tests are passed to fail()

  // fixtures
  $output->fail('A failed test', '', 0, '', '');
  $output->replay();
  // test
  $input->parse("not ok 1 - A failed test\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Failed tests without message are passed to pass()

  // fixtures
  $output->fail('', '', 0, '', '');
  $output->replay();
  // test
  $input->parse("not ok 1\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Skipped tests are passed to skip()

  // fixtures
  $output->skip('A skipped test', '', 0, '', '', 'Skip reason');
  $output->replay();
  // test
  $input->parse("ok 1 - A skipped test # SKIP Skip reason\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Skipped tests without reason are passed to skip()

  // fixtures
  $output->skip('A skipped test', '', 0, '', '', '');
  $output->replay();
  // test
  $input->parse("ok 1 - A skipped test # SKIP\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Skipped tests without message are passed to skip()

  // fixtures
  $output->skip('', '', 0, '', '', '');
  $output->replay();
  // test
  $input->parse("ok 1 # SKIP\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Todos are passed to todo()

  // fixtures
  $output->todo('A todo', '', '', '');
  $output->replay();
  // test
  $input->parse("not ok 1 - A todo # TODO Todo reason\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Todos without reason are passed to todo()

  // fixtures
  $output->todo('A todo', '', '', '');
  $output->replay();
  // test
  $input->parse("not ok 1 - A todo # TODO\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Todos without message are passed to todo()

  // fixtures
  $output->todo('', '', '', '');
  $output->replay();
  // test
  $input->parse("not ok 1 # TODO\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: The plan is ignored

  // fixtures
  $output->setExpectNothing();
  $output->replay();
  // test
  $input->parse("1..10\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Lines can be read when split

  // fixtures
  $output->pass('A passed test', '', 0, '', '');
  $output->replay();
  // test
  $input->parse("ok 1 - A p");
  $input->parse("assed test\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: Additional lines and comments are ignored

  // fixtures
  $output->setExpectNothing();
  $output->replay();
  // test
  $input->parse("Some foobar text\n");
  $input->parse("# Some comment\n");
  // assertions
  $t->ok($input->done(), 'The input is done');


// @Test: A PHP error is passed to error() - invalid identifier

  // @Test: Case 1 - Invalid identifier

  // fixtures
  $output->error(new LimeError("syntax error, unexpected T_LNUMBER, expecting T_VARIABLE or '$'", $file, 1, 'Parse error'));
  $output->replay();
  file_put_contents($file, '<?php $1invalidname;');
  $command = new LimeCommand($executable, $file);
  $command->execute();
  // test
  $input->parse($command->getOutput());


  // @Test: Case 2 - Failed require

  // fixtures
  $output->error(new LimeError("require(foobar.php): failed to open stream: No such file or directory", $file, 1, 'Warning'));
  $output->error(new LimeError("require(): Failed opening required 'foobar.php' (include_path='".get_include_path()."')", $file, 1, 'Fatal error'));
  $output->replay();
  file_put_contents($file, '<?php require "foobar.php";');
  $command = new LimeCommand($executable, $file);
  $command->execute();
  // test
  $input->parse($command->getOutput());
