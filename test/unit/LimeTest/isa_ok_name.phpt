--TEST--
isa_ok method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
class Test {}
$t->isa_ok(new Test(), 'Test', 'test name');
?>
--EXPECT--
# /test/unit/LimeTest/setup.php
ok 1 - test name
1..1
 Looks like everything went fine.
