--TEST--
like method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->like('test01', '/test\d+/');
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1
1..1
 Looks like everything went fine.
