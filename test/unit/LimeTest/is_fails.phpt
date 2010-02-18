--TEST--
is method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->is(false, true);
?>
--EXPECTF--
# /test/unit/LimeTest/setup.php
not ok 1
#     Failed test (%s/is_fails.php at line 3)
#            got: false
#       expected: true
1..1
 Looks like you failed 1 tests of 1.
