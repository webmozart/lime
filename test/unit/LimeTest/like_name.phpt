--TEST--
like method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->like('test01', '/test\d+/', 'test name');
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1 - test name
1..1
 Looks like everything went fine.
