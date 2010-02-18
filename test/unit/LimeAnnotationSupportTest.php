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


class LimeAnnotationSupportTest extends LimeTest
{
  public function isOutput($actual, $expected, $method='is')
  {
    $this->$method(trim($actual), trim($expected), 'The test file returns the expected output');
  }
}


$t = new LimeAnnotationSupportTest(41);

$root = '# /test/unit/LimeAnnotationSupport';

global $executable;
$executable = LimeExecutable::php();

function _backup($file)
{
  $file = dirname(__FILE__).'/LimeAnnotationSupport/'.$file;

  rename($file, $file.'.test.copy');
  copy($file.'.test.copy', $file);
}

function _restore($file)
{
  $file = dirname(__FILE__).'/LimeAnnotationSupport/'.$file;

  unlink($file);
  rename($file.'.test.copy', $file);
}

function _execute($file)
{
  global $executable;

  $command = new LimeCommand(dirname(__FILE__).'/LimeAnnotationSupport/'.$file, $executable);
  $command->execute();

  return $command;
}

function execute($file)
{
  _backup($file);
  $result = _execute($file);
  _restore($file);

  return $result;
}


$t->diag('Code annotated with @Before is executed once before every test');

  // test
  $command = execute($file = 'test_before.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Before
Test 1
Before
Test 2
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Code annotated with @After is executed once after every test');

  // test
  $command = execute($file = 'test_after.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Test 1
After
Test 2
After
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Code annotated with @BeforeAll is executed once before the test suite');

  // test
  $command = execute($file = 'test_before_all.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Before All
Test 1
Test 2
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Code annotated with @AfterAll is executed once after the test suite');

  // test
  $command = execute($file = 'test_after_all.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Test 1
Test 2
After All
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Code before the first annotations is executed normally');

  // test
  $command = execute($file = 'test_code_before_annotations.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Before annotation
Before
Test
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Classes can be defined before the annotations');

  // test
  $command = execute($file = 'test_class_before_annotations.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Try is not matched
If is not matched
1..0
 Looks like everything went fine.
EOF
;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Functions can be defined before the annotations');

  // test
  $command = execute($file = 'test_function_before_annotations.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Test
1..0
 Looks like everything went fine.
EOF
;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Unknown annotations result in exceptions');

  // test
  $command = execute($file = 'test_ignore_unknown.php');
  // assertion
  $t->is($command->getStatus(), 255, 'The file returned exit status 255 (dubious)');


$t->diag('Variables from the @Before scope are available in all other scopes');

  // test
  $command = execute($file = 'test_scope_before.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Before
BeforeTest
BeforeTestAfter
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Variables from the global scope are available in all other scopes');

  // test
  $command = execute($file = 'test_scope_global.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Global
GlobalBefore
GlobalBeforeTest
GlobalBeforeTestAfter
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');


$t->diag('Variables from other annotations are NOT available in all other scopes');

  // test
  $command = execute($file = 'test_scope_private.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Is not set
Is not set
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Tests annotated with @Test may have comments');

  // test
  $command = execute($file = 'test_comments.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Test 1
# This test is commented with "double" and 'single' quotes
Test 2
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Exceptions can be expected');

  // test
  $command = execute($file = 'test_expect.php');
  // assertion
  $expected = '/'.str_replace('%ANY%', '.*', preg_quote(<<<EOF
$root/@$file
Test 1
not ok 1 - A "RuntimeException" was thrown
#     Failed test (%ANY%)
#            got: 'none'
#       expected: 'RuntimeException'
Test 2
ok 2 - A "RuntimeException" was thrown
1..2
 Looks like you failed 1 tests of 2.
EOF
, '/')).'/';
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected, 'like');


$t->diag('Exception objects can be expected');

  // test
  $command = execute($file = 'test_expect_object.php');
  // assertion
  $expected = '/'.str_replace('%ANY%', '.*', preg_quote(<<<EOF
$root/@$file
Test 1
ok 1 - A "RuntimeException" was thrown
Test 2
not ok 2 - A "RuntimeException" was thrown
#     Failed test (%ANY%)
#            got: object(RuntimeException) (
#                   ...
#                   'code' => 0,
#                 )
#       expected: object(RuntimeException) (
#                   ...
#                   'code' => 1,
#                 )
Test 3
ok 3 - A "RuntimeException" was thrown
1..3
 Looks like you failed 1 tests of 3.
EOF
, '/')).'/';
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected, 'like');


$t->diag('Old expected exceptions are ignored');

  // test
  $command = execute($file = 'test_expect_ignore_old.php');
  // assertion
  $expected = '/'.str_replace('%ANY%', '.*', preg_quote(<<<EOF
$root/@$file
Test 1
ok 1 - A "RuntimeException" was thrown
Test 2
not ok 2 - A "LogicException" was thrown
#     Failed test (%ANY%)
#            got: 'none'
#       expected: 'LogicException'
1..2
 Looks like you failed 1 tests of 2.
EOF
, '/')).'/';
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected, 'like');


$t->diag('Annotations can be commented out with /*...*/');

  // test
  $command = execute($file = 'test_multiline_comments.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Test 1
Test 3
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('Test files remain unchanged when fatal errors occur');

  // fixtures
  $expected = file_get_contents(dirname(__FILE__).'/LimeAnnotationSupport/test_fatal_error.php');
  _backup('test_fatal_error.php');
  // test
  _execute($file = 'test_fatal_error.php');
  // assertions
  $content = file_get_contents(dirname(__FILE__).'/LimeAnnotationSupport/test_fatal_error.php');
  $t->is($content, $expected, 'The file content remained unchanged');
  // teardown
  _restore('test_fatal_error.php');


$t->diag('Test files remain unchanged when fatal errors in combination with require statements occur');

  // fixtures
  $expected = file_get_contents(dirname(__FILE__).'/LimeAnnotationSupport/test_fatal_require.php');
  _backup('test_fatal_require.php');
  // test
  _execute($file = 'test_fatal_require.php');
  // assertions
  $content = file_get_contents(dirname(__FILE__).'/LimeAnnotationSupport/test_fatal_require.php');
  $t->is($content, $expected, 'The file content remained unchanged');
  // teardown
  _restore('test_fatal_require.php');


$t->diag('Test files remain unchanged when fatal errors in combination with undefined variables occur');

  // fixtures
  $expected = file_get_contents(dirname(__FILE__).'/LimeAnnotationSupport/test_fatal_undefined.php');
  _backup('test_fatal_undefined.php');
  // test
  _execute($file = 'test_fatal_undefined.php');
  // assertions
  $content = file_get_contents(dirname(__FILE__).'/LimeAnnotationSupport/test_fatal_undefined.php');
  $t->is($content, $expected, 'The file content remained unchanged');
  // teardown
  _restore('test_fatal_undefined.php');


$t->diag('Line numbers in error messages remain the same as in the original files');

  // test
  $command = execute($file = 'test_line_number.php');
  // assertion
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), '/on line 26(?!\d)/', 'like');


$t->diag('The last line in an annotated file can be a comment (bugfix)');

  // test
  $command = execute($file = 'test_last_line_commented.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Test
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('The annotation support can be enabled in included bootstrap files');

  // test
  $command = execute($file = 'test_include.php');
  // assertion
  $expected = <<<EOF
$root/@$file
Before
Test 1
Before
Test 2
1..0
 Looks like everything went fine.
EOF;
  $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
  $t->isOutput($command->getOutput(), $expected);


$t->diag('The annotation support is able to deal with closures');

  if (version_compare(PHP_VERSION, '5.3', '>='))
  {
    // test
    $command = execute($file = 'test_closure.php');
    // assertion
    $expected = <<<EOF
$root/@$file
Test 1
1..0
 Looks like everything went fine.
EOF;
    $t->is($command->getStatus(), 0, 'The file returned exit status 0 (success)');
    $t->isOutput($command->getOutput(), $expected);
  }
  else
  {
    $t->skip();
    $t->skip();
  }