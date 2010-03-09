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

$t = new LimeTest();


// @Before

  $executable = new LimeExecutable(LimeExecutable::php() . ' %file%');


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
  $command = new LimeCommand($executable, $file);
  $command->execute();
  // assertions
  $t->is($command->getOutput(), 'Test', 'The output is correct');
  $t->is($command->getErrors(), 'Errors', 'The errors are correct');
  $t->is($command->getStatus(), 1, 'The return value is correct');

