--TEST--
ok method with default message
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->ok(1);
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1
1..1
 Looks like everything went fine.
