--TEST--
isnt method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->isnt(false, true);
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1
1..1
 Looks like everything went fine.
