--TEST--
like method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->unlike('test01', '/test\d+/');
?>
--EXPECTF--
# /test/unit/LimeTest/setup.php
not ok 1
#     Failed test (%s/unlike_fails.php at line 3)
#                'test01'
#       matches '/test\d+/'
1..1
 Looks like you failed 1 tests of 1.
