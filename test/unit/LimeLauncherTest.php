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

  $output = $t->mock('LimeOutputInterface');
  $inputFactory = new LimeInputFactory();
  $executable = new LimeExecutable(LimeExecutable::php().' %file%', 'raw');
  $file = tempnam(sys_get_temp_dir(), 'lime');
  $launcher = new LimeLauncher($output, $inputFactory);


// @After

  $file = null;
  $output = null;
  $launcher = null;


// @Test: If the output cannot be unserialized, an error is reported

  // fixtures
  file_put_contents($file, '<?php echo "Some Error occurred\n";');
  $output->error(new LimeError('Could not parse test output: "Some Error occurred"', $file, 1, 'Warning'));
  $output->replay();
  // test
  $launcher->launch(new LimeFile($file, $executable));
  while (!$launcher->done()) $launcher->proceed();


// @Test: Data sent to the error stream is passed to error() line by line

  // fixtures
  file_put_contents($file, '<?php file_put_contents("php://stderr", "Error 1\nError 2");');
  $output->error(new LimeError('Error 1', $file, 0));
  $output->error(new LimeError('Error 2', $file, 0));
  $output->replay();
  // test
  $launcher->launch(new LimeFile($file, $executable));
  while (!$launcher->done()) $launcher->proceed();


// @Test: PHP errors/warnings are passed to error()/warning()

  // @Test: Case 1 - Invalid identifier

  // fixtures
  file_put_contents($file, '<?php $1invalidname;');
  $output->error(new LimeError("syntax error, unexpected T_LNUMBER, expecting T_VARIABLE or '$'", $file, 1, 'Parse error'));
  $output->replay();
  // test
  $launcher->launch(new LimeFile($file, $executable));
  while (!$launcher->done()) $launcher->proceed();


  // @Test: Case 2 - Failed require

  // fixtures
  file_put_contents($file, '<?php require "foobar.php";');
  $output->error(new LimeError("require(foobar.php): failed to open stream: No such file or directory", $file, 1, 'Warning'));
  $output->error(new LimeError("require(): Failed opening required 'foobar.php' (include_path='".get_include_path()."')", $file, 1, 'Fatal error'));
  $output->replay();
  // test
  $launcher->launch(new LimeFile($file, $executable));
  while (!$launcher->done()) $launcher->proceed();


