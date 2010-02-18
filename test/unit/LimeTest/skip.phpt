--TEST--
skip method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->skip();
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1 # SKIP
1..1
 Looks like everything went fine.
