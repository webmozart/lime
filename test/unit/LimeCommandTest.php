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

LimeAnnotationSupport::enable();

$t = new LimeTest(5);


// @Before

  $executable = LimeExecutable::php();


// @Test: A PHP file can be executed

  // fixtures
  $file = tempnam(sys_get_temp_dir(), 'lime');
  file_put_contents($file, <<<EOF
<?php
echo "Test";
file_put_contents("php://stderr", "Errors");
exit(1);
EOF
  );
  // test
  $command = new LimeCommand($file, $executable);
  $command->execute();
  // assertions
  $t->is($command->getOutput(), 'Test', 'The output is correct');
  $t->is($command->getErrors(), 'Errors', 'The errors are correct');
  $t->is($command->getStatus(), 1, 'The return value is correct');


// @Test: A PHP file can be executed with arguments

  // fixtures
  $file = tempnam(sys_get_temp_dir(), 'lime');
  file_put_contents($file, <<<EOF
<?php
unset(\$GLOBALS['argv'][0]);
var_export(\$GLOBALS['argv']);
exit(1);
EOF
  );
  // test
  $command = new LimeCommand($file, $executable, array('--test' => true, '--arg' => 'value'));
  $command->execute();
  // assertions
  $output = "array (
  1 => '--test',
  2 => '--arg=value',
)";
  $t->is($command->getOutput(), $output, 'The output is correct');
  $t->is($command->getStatus(), 1, 'The return value is correct');