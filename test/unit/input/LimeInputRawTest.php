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
  $input = new LimeInputRaw($output);


// @After

  $file = null;
  $output = null;
  $input = null;


// @Test: The call to plan() is passed

  // fixtures
  $output->plan(1, '/test/file');
  $output->replay();
  // test
  $input->parse(serialize(array("plan", array(1, "/test/file")))."\n");


// @Test: The call to error() is passed

  // fixtures
  $output->error(new LimeError("An error", "/test/file", 11));
  $output->replay();
  // test
  $input->parse(serialize(array("error", array(new LimeError("An error", "/test/file", 11))))."\n");


// @Test: The call to pass() is passed

  // fixtures
  $output->pass('A passed test', '/test/file', 11);
  $output->replay();
  // test
  $input->parse(serialize(array("pass", array("A passed test", "/test/file", 11)))."\n");


// @Test: Two arrays are converted to two method calls

  // fixtures
  $output->pass('A passed test', '/test/file', 11);
  $output->pass('Another passed test', '/test/file', 11);
  $output->replay();
  // test
  $input->parse(serialize(array("pass", array("A passed test", "/test/file", 11)))."\n".serialize(array("pass", array("Another passed test", "/test/file", 11)))."\n");


// @Test: A split serialized array can be read correctly

  // fixtures
  $output->pass('A passed test', '/test/file', 11);
  $output->replay();
  // test
  $serialized = serialize(array("pass", array("A passed test", "/test/file", 11)))."\n";
  $strings =  str_split($serialized, strlen($serialized)/2 + 1);
  $input->parse($strings[0]);
  $input->parse($strings[1]);


// @Test: Escaped arguments are unescaped

  // fixtures
  $output->comment("A \\n\\r comment \n with line \r breaks");
  $output->replay();
  // test
  $input->parse(addcslashes(serialize(array("comment", array("A \\\\n\\\\r comment \\n with line \\r breaks"))), '//')."\n");


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
  $output->warning("Warning: require(foobar.php): failed to open stream: No such file or directory", $file, 1);
  $output->error(new LimeError("require(): Failed opening required 'foobar.php' (include_path='".get_include_path()."')", $file, 1, 'Fatal error'));
  $output->replay();
  file_put_contents($file, '<?php require "foobar.php";');
  $command = new LimeCommand($executable, $file);
  $command->execute();
  // test
  $input->parse($command->getOutput());
