<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bernhard.schussek@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../MockLimeOutput.php';

LimeAnnotationSupport::enable();


interface TestInterface
{
  public function testMethod($parameter);
}

interface TestInterfaceWithTypeHints
{
  public function testMethod(stdClass $object, array $array);
}

interface TestInterfaceWithDefaultValues
{
  public function testMethod($null = null, $int = 1, $bool = true, $string = 'String', $float = 1.1);
}

interface TestInterfaceWithReferenceParameters
{
  public function testMethod(&$parameter);
}

abstract class TestClassAbstract
{
  abstract public function testMethod($parameter);
}

class TestClass
{
  public $calls = 0;
  public $calls2 = 0;

  public function testMethod()
  {
    ++$this->calls;
  }

  public function testMethod2()
  {
    ++$this->calls2;
  }
}

class TestClassWithMethodsFromMock
{
  public function __construct() {}
  public function __call($method, $args) {}
  public function __lime_replay() {}
  public function __lime_getState() {}
}

class TestClassWithControlMethods
{
  public function replay() {}
}

class TestClassWithFinalMethods
{
  public static $calls = 0;

  public final function testMethod()
  {
    ++self::$calls;
  }
}

class TestException extends Exception {}

class TestCallbackClass
{
  public static $arguments;

  public static function callback()
  {
    self::$arguments = func_get_args();

    return 'elvis is alive';
  }
}

class TestAutoloader
{
  public static $calls = null;

  public static function autoload($class)
  {
    ++self::$calls;
  }
}

spl_autoload_register(array('TestAutoloader', 'autoload'));


$t = new LimeTest(106);


// @Before

  $output = new MockLimeOutput();
  $m = LimeMock::create('TestClass', $output);


// @After

  $output = null;
  $m = null;


// @Test: Interfaces can be mocked

  // test
  $m = LimeMock::create('TestInterface', $output);
  // assertions
  $t->ok($m instanceof TestInterface, 'The mock implements the interface');
  $t->ok($m instanceof LimeMockInterface, 'The mock implements "LimeMockInterface"');


// @Test: Namespaced interfaces can be mocked (PHP 5.3)

  if (version_compare(PHP_VERSION, '5.3', '>='))
  {
    require_once __DIR__.'/php5.3/TestInterface.php';

    // test
    $m = LimeMock::create('TestNamespace\TestSubNamespace\TestInterface', $output);
    // assertions
    $interface = 'TestNamespace\TestSubNamespace\TestInterface';
    $t->ok($m instanceof $interface, 'The mock implements the interface');
    $t->ok($m instanceof LimeMockInterface, 'The mock implements "LimeMockInterface"');
  }
  else
  {
    $t->skip();
    $t->skip();
  }


// @Test: Abstract classes can be mocked

  // test
  $m = LimeMock::create('TestClassAbstract', $output);
  // assertions
  $t->ok($m instanceof TestClassAbstract, 'The mock inherits the class');
  $t->ok($m instanceof LimeMockInterface, 'The mock implements "LimeMockInterface"');


// @Test: Non-existing classes can be mocked

  $m = LimeMock::create('FoobarClass', $output);
  // assertions
  $t->ok($m instanceof FoobarClass, 'The mock generates and inherits the class');
  $t->ok($m instanceof LimeMockInterface, 'The mock implements "LimeMockInterface"');


// @Test: Non-existing namespaced classes can be mocked (PHP 5.3)

  if (version_compare(PHP_VERSION, '5.3', '>='))
  {
    // test
    $m = LimeMock::create('TestNamespace\TestSubNamespace\TestClass', $output);
    // assertions
    $class = 'TestNamespace\TestSubNamespace\TestClass';
    $t->ok($m instanceof $class, 'The mock generates and inherits the class');
    $t->ok($m instanceof LimeMockInterface, 'The mock implements "LimeMockInterface"');
  }
  else
  {
    $t->skip();
    $t->skip();
  }


// @Test: Classes with methods from the mock can be mocked

  $m = LimeMock::create('TestClassWithMethodsFromMock', $output);
  // assertions
  $t->ok($m instanceof TestClassWithMethodsFromMock, 'The mock generates and inherits the class');
  $t->ok($m instanceof LimeMockInterface, 'The mock implements "LimeMockInterface"');


// @Test: Methods with type hints can be mocked

  // test
  $m = LimeMock::create('TestInterfaceWithTypeHints', $output);
  // assertions
  $t->ok($m instanceof TestInterfaceWithTypeHints, 'The mock implements the interface');


// @Test: Methods with reference parameters can be mocked

  // test
  $m = LimeMock::create('TestInterfaceWithReferenceParameters', $output);
  // assertions
  $t->ok($m instanceof TestInterfaceWithReferenceParameters, 'The mock implements the interface');


// @Test: Methods with default values can be mocked

  // test
  $m = LimeMock::create('TestInterfaceWithDefaultValues', $output);
  // assertions
  $t->ok($m instanceof TestInterfaceWithDefaultValues, 'The mock implements the interface');


// @Test: Mocking of classes does not trigger autoloading

  // fixtures
  TestAutoloader::$calls = 0;
  // test
  $m = LimeMock::create('Foobar', $output);
  // assertions
  $t->is(TestAutoloader::$calls, 0, 'The autoloader was not called');


// @Test: Methods in the mocked class are not called

  // fixtures
  $m->calls = 0;
  // test
  $m->testMethod();
  $m->replay();
  $m->testMethod();
  // assertions
  $t->is($m->calls, 0, 'The method has not been called');


// @Test: Unmocked methods in the mocked class are called if the option "stub_methods" is FALSE

  // fixtures
  $m->calls = 0;
  $m->calls2 = 0;
  // test
  $m = LimeMock::create('TestClass', $output, array('stub_methods' => false, 'nice' => true));
  $m->testMethod();
  $m->replay();
  $m->testMethod();
  $m->testMethod2();
  // assertions
  $t->is($m->calls, 0, 'The mocked method has not been called');
  $t->is($m->calls2, 1, 'The unmocked method has been called');


// @Test: Final methods cannot be mocked

  // fixtures
  TestClassWithFinalMethods::$calls = 0;
  $m = LimeMock::create('TestClassWithFinalMethods', $output);
  $m->replay();
  // test
  $m->testMethod();
  // assertions
  $t->is(TestClassWithFinalMethods::$calls, 1, 'The method has been called');


// @Test: Return values can be stubbed

  // test
  $m->testMethod()->returns('Foobar');
  $m->replay();
  $value = $m->testMethod();
  // assertions
  $t->is($value, 'Foobar', 'The correct value has been returned');


// @Test: Return values can be stubbed based on method parameters

  // test
  $m->testMethod()->returns('Foobar');
  $m->testMethod(1)->returns('More foobar');
  $m->replay();
  $value1 = $m->testMethod();
  $value2 = $m->testMethod(1);
  // assertions
  $t->is($value1, 'Foobar', 'The correct value has been returned');
  $t->is($value2, 'More foobar', 'The correct value has been returned');


// @Test: Exceptions can be stubbed

  // fixtures
  $m->testMethod()->throws('TestException');
  $m->replay();
  $t->expect('TestException');
  // test
  $m->testMethod();


// @Test: Exceptions can be stubbed using objects

  // fixtures
  $m->testMethod()->throws(new TestException());
  $m->replay();
  $t->expect('TestException'); // TODO: It would be good if we could test for the exact object
  // test
  $m->testMethod();


// @Test: ->verify() fails if a method was not called

  // test
  $m->testMethod();
  $m->replay();
  $m->verify();
  // assertions
  $t->is($output->fails, 1, 'One test failed');
  $t->is($output->passes, 0, 'No test passed');


// @Test: ->verify() passes if a method was called correctly

  // test
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $m->testMethod(1, 'Foobar');
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: ->verify() passes if two methods were called correctly

  // test
  $m->testMethod1();
  $m->testMethod2('Foobar');
  $m->replay();
  $m->testMethod1();
  $m->testMethod2('Foobar');
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: ->verify() passes if a method was expected and called several times

  // test
  $m->testMethod1();
  $m->testMethod1();
  $m->replay();
  $m->testMethod1();
  $m->testMethod1();
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: After verifying all method calls are ignored

  // test
  $m->testMethod();
  $m->replay();
  $m->testMethod();
  $m->verify();
  $m->foobar();
  $m->hurray();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: A mock can be reset in record mode

  // test
  $m->someOtherMethod();
  $m->reset();
  $m->testMethod();
  $m->replay();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: A mock can be reset in replay mode

  // test
  $m->someOtherMethod();
  $m->replay();
  $m->reset();
  $m->testMethod();
  $m->replay();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: An exception is thrown if a method is called with wrong parameters

  // fixture
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->testMethod(1);


// @Test: An exception is thrown if a method is called with the right parameters in a wrong order

  // fixture
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->testMethod('Foobar', 1);


// @Test: The option "no_exceptions" suppresses exceptions upon method calls

  // test
  $m = LimeMock::create('TestClass', $output, array('no_exceptions' => true));
  $m->testMethod('Foobar');
  $m->replay();
  $m->testMethod('Foobar', 1);
  $m->verify();
  // assertions
  $t->is($output->fails, 1, 'One test failed');
  $t->is($output->passes, 0, 'No test passed');


// @Test: A method can be expected twice with different parameters

  // @Test: - Case 1: Insufficient method calls

  // test
  $m->testMethod();
  $m->testMethod('Foobar');
  $m->replay();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 1, 'One test failed');


  // @Test: - Case 2: Sufficient method calls

  // test
  $m->testMethod();
  $m->testMethod('Foobar');
  $m->replay();
  $m->testMethod();
  $m->testMethod('Foobar');
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: Methods may be called in any order

  // test
  $m->testMethod1();
  $m->testMethod2();
  $m->replay();
  $m->testMethod2();
  $m->testMethod1();
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: By default, method parameters are compared with weak typing

  // test
  $m->testMethod(1);
  $m->replay();
  $m->testMethod('1');
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: ->times()

  // @Test: - Case 1: Too few actual calls

  // test
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($output->passes, 0, 'No test passed');
  $t->is($output->fails, 1, 'One test failed');


  // @Test: - Case 2: Too many actual calls

  // fixture
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->testMethod(1);
  $t->expect('LimeMockException');
  // test
  $m->testMethod(1);


  // @Test: - Case 3: Correct number

  // test
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


  // @Test: - Case 4: Call with different parameters

  // fixture
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $t->expect('LimeMockException');
  // test
  $m->testMethod();


// @Test: ->atLeastOnce()

  // @Test: - Case 1: Zero actual calls

  $m->testMethod(1)->atLeastOnce();
  $m->replay();
  $m->verify();
  // assertions
  $t->is($output->passes, 0, 'No test passed');
  $t->is($output->fails, 1, 'One test failed');

  // @Test: - Case 2: One actual call

  $m->testMethod(1)->atLeastOnce();
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');

  // @Test: - Case 3: Two actual calls

  $m->testMethod(1)->atLeastOnce();
  $m->replay();
  $m->testMethod(1);
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: ->times() and ->returns()

  // test
  $m->testMethod(1)->returns('Foobar')->times(2);
  $m->replay();
  $value1 = $m->testMethod(1);
  $value2 = $m->testMethod(1);
  // assertions
  $t->is($value1, 'Foobar', 'The first return value is correct');
  $t->is($value2, 'Foobar', 'The second return value is correct');


// @Test: ->withAnyParameters()

  // @Test: - Case 1: Correct parameters

  // test
  $m->method('testMethod');
  $m->replay();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');

  // @Test: - Case 1: "Wrong" parameters

  // test
  $m->method('testMethod');
  $m->replay();
  $m->testMethod(1, 2, 3);
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: ->between()

  // @Test: - Case 1: Too few calls

  // test
  $m->testMethod()->between(2, 4);
  $m->replay();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($output->passes, 0, 'No test passed');
  $t->is($output->fails, 1, 'One test failed');

  // @Test: - Case 2: Correct number

  // test
  $m->testMethod()->between(2, 4);
  $m->replay();
  $m->testMethod();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');

  // @Test: - Case 3: Another correct number

  // test
  $m->testMethod()->between(2, 4);
  $m->replay();
  $m->testMethod();
  $m->testMethod();
  $m->testMethod();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');

  // @Test: - Case 4: Too many calls

  // test
  $m->testMethod()->between(2, 4);
  $m->replay();
  $m->testMethod();
  $m->testMethod();
  $m->testMethod();
  $m->testMethod();
  $t->expect('LimeMockException');
  $m->testMethod();


// @Test: ->never()

  // @Test: - Case 1: No actual call

  // test
  $m->testMethod(1, 2, 3);
  $m->testMethod()->never();
  $m->replay();
  $m->testMethod(1, 2, 3);
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');

  // @Test: - Case 2: Any actual calls

  // test
  $m->testMethod(1, 2, 3);
  $m->testMethod()->never();
  $m->replay();
  $m->testMethod(1, 2, 3);
  $t->expect('LimeMockException');
  $m->testMethod();


// @Test: ->method() always passes, regardless of how often a method was called

  // @Test: - Case 1: No actual call

  // test
  $m->testMethod(1, 2, 3);
  $m->testMethod()->any();
  $m->replay();
  $m->testMethod(1, 2, 3);
  $m->verify();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');

  // @Test: - Case 2: Any actual calls

  // test
  $m->testMethod(1, 2, 3);
  $m->testMethod()->any();
  $m->replay();
  $m->testMethod(1, 2, 3);
  $m->testMethod();
  // assertions
  $t->is($output->passes, 2, 'Two tests passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: ->strict() enforces strict parameter checks for single methods

  // @Test: - Case 1: Type comparison fails

  // fixture
  $m->testMethod(1)->strict();
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->testMethod('1');


  // @Test: - Case 2: Type comparison passes

  // test
  $m->testMethod(1)->strict();
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: ->parameter() tests single parameters of a method invocation

  // @Test: - Case 1: Comparison fails

  // fixture
  $m->method('testMethod')->parameter(2)->is('foo');
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->testMethod(1, 'bar');


  // @Test: - Case 2: Comparison passes

  // fixture
  $m->method('testMethod')->parameter(2)->is('foo');
  $m->replay();
  $m->testMethod(1, 'foo');
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


  // @Test: - Case 3: Parameter offset is out of range

  // fixture
  $m->method('testMethod')->parameter(2)->is('foo');
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->testMethod(1);


// @Test: If a class with the mock's control methods is mocked, an exception is thrown

  // test
  $t->expect('LogicException');
  $m = LimeMock::create('TestClassWithControlMethods', $output);


// @Test: If a class with the mock's control methods is mocked and "generate_controls" is set to false, no exception is thrown

  // test
  $m = LimeMock::create('TestClassWithControlMethods', $output, array('generate_controls' => false));
  $t->pass('No exception is thrown');


// @Test: The control methods like ->replay() can be mocked

  // fixtures
  $m = LimeMock::create('TestClass', $output, array('generate_controls' => false));
  // test
  $m->replay()->returns('Foobar');
  LimeMock::replay($m);
  $value = $m->replay();
  // assertions
  $t->is($value, 'Foobar', 'The return value was correct');


// @Test: If no method call is expected, all method calls are ignored

  // test
  $m->replay();
  $m->testMethod1();
  $m->testMethod2(1, 'Foobar');
  $m->verify();
  // assertions
  $t->is($output->passes, 0, 'No test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: If setExpectNothing() is called, no method must be called

  // fixture
  $m->setExpectNothing();
  $m->replay();
  $t->expect('LimeMockException');
  // test
  $m->testMethod();


// @Test: Mock methods can call other callbacks

  // fixtures
  TestCallbackClass::$arguments = null;
  // test
  $m->testMethod(1, 'foobar')->callback(array('TestCallbackClass', 'callback'));
  $m->replay();
  $value = $m->testMethod(1, 'foobar');
  // assertions
  $t->is(TestCallbackClass::$arguments, array(1, 'foobar'), 'The arguments have been passed to the callback');
  $t->is($value, 'elvis is alive', 'The return value of the callback has been passed through');


// @Test: If a callback AND a return value is configured, the return value of the callback is ignored

  // test
  $m->testMethod()->callback(array('TestCallbackClass', 'callback'))->returns('elvis sure is dead');
  $m->replay();
  $value = $m->testMethod();
  // assertions
  $t->is($value, 'elvis sure is dead', 'The return value of the callback was ignored');


// @Test: The return value of the callback is ignored even if the configured return value is NULL

  // test
  $m->testMethod()->callback(array('TestCallbackClass', 'callback'))->returns(null);
  $m->replay();
  $value = $m->testMethod();
  // assertions
  $t->same($value, null, 'The return value of the callback was ignored');


// @Test: Parameters are passed to the callback correctly, if any parameters are expected

  // fixtures
  TestCallbackClass::$arguments = null;
  // test
  $m->method('testMethod')->callback(array('TestCallbackClass', 'callback'));
  $m->replay();
  $value = $m->testMethod(1, 'foobar');
  // assertions
  $t->is(TestCallbackClass::$arguments, array(1, 'foobar'), 'The arguments have been passed to the callback');
  $t->is($value, 'elvis is alive', 'The return value of the callback has been passed through');


// @Test: Methods may be called any number of times if the option "nice" is set

  // test
  $m = LimeMock::create('TestClass', $output, array('nice' => true));
  $m->testMethod();
  $m->replay();
  $m->testMethod();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: Unexpected method calls are ignored if the option "nice" is set

  // test
  $m = LimeMock::create('TestClass', $output, array('nice' => true));
  $m->testMethod();
  $m->replay();
  $m->testMethod();
  $m->testMethod(1, 2, 3);
  $m->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');

