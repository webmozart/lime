--TEST--
cmp_ok method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->cmp_ok(2, '>', 1);
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1
1..1
 Looks like everything went fine.
