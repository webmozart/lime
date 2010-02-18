--TEST--
like method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->like('tests01', '/test\d+/');
?>
--EXPECTF--
# /test/unit/LimeTest/setup.php
not ok 1
#     Failed test (%s/like_fails.php at line 3)
#                     'tests01'
#       doesn't match '/test\d+/'
1..1
 Looks like you failed 1 tests of 1.
