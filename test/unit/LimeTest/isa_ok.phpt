--TEST--
isa_ok method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
class Test {}
$t->isa_ok(new Test(), 'Test');
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1
1..1
 Looks like everything went fine.
