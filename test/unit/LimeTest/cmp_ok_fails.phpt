--TEST--
cmp_ok method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->cmp_ok(1, '>', 1);
?>
--EXPECTF--
# /test/unit/LimeTest/setup.php
not ok 1
#     Failed test (%s/cmp_ok_fails.php at line 3)
#                1
#       is not > 1
1..1
 Looks like you failed 1 tests of 1.
