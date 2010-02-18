--TEST--
skip method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->skip('test name', 2);
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1 - test name # SKIP
ok 2 - test name # SKIP
1..2
 Looks like everything went fine.
