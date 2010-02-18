--TEST--
isa_ok method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
class Test {}
$t->isa_ok(new Test(), 'Foo');
?>
--EXPECTF--
# /test/unit/LimeTest/setup.php
not ok 1
#     Failed test (%s/isa_ok_fails.php at line 4)
#       variable isn't a 'Foo' it's a 'Test'
1..1
 Looks like you failed 1 tests of 1.
