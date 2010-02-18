--TEST--
pass method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->pass();
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1
1..1
 Looks like everything went fine.
