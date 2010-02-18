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

$t = new LimeTest(34);


// @Before

  $executable = LimeExecutable::php();
  $file = tempnam(sys_get_temp_dir(), 'lime');
  $output = $t->mock('LimeOutputInterface');
  $parser = new LimeParserTap($output);


// @After

  $file = null;
  $output = null;
  $parser = null;


// @Test: Successful tests are passed to pass()

  // fixtures
  $output->pass('A passed test', '', '');
  $output->replay();
  // test
  $parser->parse("ok 1 - A passed test\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Successful tests without message are passed to pass()

  // fixtures
  $output->pass('', '', '');
  $output->replay();
  // test
  $parser->parse("ok 1\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Failed tests are passed to fail()

  // fixtures
  $output->fail('A failed test', '', '');
  $output->replay();
  // test
  $parser->parse("not ok 1 - A failed test\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Failed tests without message are passed to pass()

  // fixtures
  $output->fail('', '', '');
  $output->replay();
  // test
  $parser->parse("not ok 1\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Skipped tests are passed to skip()

  // fixtures
  $output->skip('A skipped test', '', '');
  $output->replay();
  // test
  $parser->parse("ok 1 - A skipped test # SKIP Skip reason\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Skipped tests without reason are passed to skip()

  // fixtures
  $output->skip('A skipped test', '', '');
  $output->replay();
  // test
  $parser->parse("ok 1 - A skipped test # SKIP\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Skipped tests without message are passed to skip()

  // fixtures
  $output->skip('', '', '');
  $output->replay();
  // test
  $parser->parse("ok 1 # SKIP\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Skipped tests are passed to skip() and warning() when status is "not ok"

  // fixtures
  $output->skip('A skipped test', '', '');
  $output->warning('Skipped tests are expected to have status "ok"', '', '');
  $output->replay();
  // test
  $parser->parse("not ok 1 - A skipped test # SKIP Skip reason\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Todos are passed to todo()

  // fixtures
  $output->todo('A todo', '', '');
  $output->replay();
  // test
  $parser->parse("not ok 1 - A todo # TODO Todo reason\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Todos without reason are passed to todo()

  // fixtures
  $output->todo('A todo', '', '');
  $output->replay();
  // test
  $parser->parse("not ok 1 - A todo # TODO\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Todos without message are passed to todo()

  // fixtures
  $output->todo('', '', '');
  $output->replay();
  // test
  $parser->parse("not ok 1 # TODO\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Todos are passed to todo() and warning() when status is "ok"

  // fixtures
  $output->todo('A todo', '', '');
  $output->warning('TODOs are expected to have status "not ok"', '', '');
  $output->replay();
  // test
  $parser->parse("ok 1 - A todo # TODO Todo reason\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: The plan is passed to plan()

  // fixtures
  $output->plan(10);
  $output->replay();
  // test
  $parser->parse("1..10\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Lines can be read when split

  // fixtures
  $output->pass('A passed test', '', '');
  $output->replay();
  // test
  $parser->parse("ok 1 - A p");
  $parser->parse("assed test\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: Additional lines and comments are ignored

  // fixtures
  $output->setExpectNothing();
  $output->replay();
  // test
  $parser->parse("Some foobar text\n");
  $parser->parse("# Some comment\n");
  // assertions
  $t->ok($parser->done(), 'The parser is done');
  $output->verify();


// @Test: A PHP error is passed to error() - invalid identifier

  // @Test: Case 1 - Invalid identifier

  // fixtures
  $output->error(new LimeError("syntax error, unexpected T_LNUMBER, expecting T_VARIABLE or '$'", $file, 1, 'Parse error'));
  $output->replay();
  file_put_contents($file, '<?php $1invalidname;');
  $command = new LimeCommand($file, $executable);
  $command->execute();
  // test
  $parser->parse($command->getOutput());
  // assertions
  $output->verify();


  // @Test: Case 2 - Failed require

  // fixtures
  $output->warning("Warning: require(foobar.php): failed to open stream: No such file or directory", $file, 1);
  $output->error(new LimeError("require(): Failed opening required 'foobar.php' (include_path='".get_include_path()."')", $file, 1, 'Fatal error'));
  $output->replay();
  file_put_contents($file, '<?php require "foobar.php";');
  $command = new LimeCommand($file, $executable);
  $command->execute();
  // test
  $parser->parse($command->getOutput());
  // assertions
  $output->verify();