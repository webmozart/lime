--TEST--
can_ok method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
class Test { function test() {} }
$t->can_ok(new Test(), 'foo');
?>
--EXPECTF--
# /test/unit/LimeTest/setup.php
not ok 1
#     Failed test (%s/can_ok_fails.php at line 4)
#       method 'foo' does not exist
1..1
 Looks like you failed 1 tests of 1.
