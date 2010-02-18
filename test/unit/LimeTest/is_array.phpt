--TEST--
is method with array
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->is(array(0 => 1), array(0 => 2));
?>
--EXPECTF--
# /test/unit/LimeTest/setup.php
not ok 1
#     Failed test (%s/is_array.php at line 3)
#            got: array (
#                   0 => 1,
#                 )
#       expected: array (
#                   0 => 2,
#                 )
1..1
 Looks like you failed 1 tests of 1.
