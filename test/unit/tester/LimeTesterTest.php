<?php

/*
 * This file is part of the Lime framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Bernhard Schussek <bernhard.schussek@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include dirname(__FILE__).'/../../bootstrap/unit.php';

LimeAnnotationSupport::enable();

interface TestInterface {}
class TestInterfaceClass implements TestInterface {}
class TestInterfaceTester extends LimeTesterObject {}

class TestClass {}
class TestSubClass extends TestClass {}
class TestClassTester extends LimeTesterObject {}
class InvalidTester {}

$t = new LimeTest(15);


// Don't use other test methods than ->ok() here, because the testers tested
// here are the foundation for the other test methods


// @Test: create() creates a LimeTesterScalar for booleans

  $expected = new LimeTesterScalar(true);
  $t->ok(LimeTester::create(true) == $expected, 'The correct object was created');
  $expected = new LimeTesterScalar(false);
  $t->ok(LimeTester::create(false) == $expected, 'The correct object was created');


// @Test: create() creates a LimeTesterString for strings

  $expected = new LimeTesterString('My string');
  $t->ok(LimeTester::create('My string') == $expected, 'The correct object was created');


// @Test: create() creates a LimeTesterInteger for integers

  $expected = new LimeTesterInteger(12);
  $t->ok(LimeTester::create(12) == $expected, 'The correct object was created');


// @Test: create() creates a LimeTesterScalar for null values

  $expected = new LimeTesterScalar(null);
  $t->ok(LimeTester::create(null) == $expected, 'The correct object was created');


// @Test: create() creates a LimeTesterDouble for doubles

  $expected = new LimeTesterDouble(1.2);
  $t->ok(LimeTester::create(1.2) == $expected, 'The correct object was created');


// @Test: create() creates a LimeTesterArray for arrays

  $expected = new LimeTesterArray(array());
  $t->ok(LimeTester::create(array()) == $expected, 'The correct object was created');


// @Test: create() creates a LimeTesterObject for objects

  $object = new stdClass();
  $expected = new LimeTesterObject($object);
  $t->ok(LimeTester::create($object) == $expected, 'The correct object was created');


// @Test: create() creates a LimeTesterResource for resources

  $resource = tmpfile();
  $expected = new LimeTesterResource($resource);
  $t->ok(LimeTester::create($resource) == $expected, 'The correct object was created');
  fclose($resource);


// @Test: register() registers a new tester for a given class

  LimeTester::register('TestClass', 'TestClassTester');
  $object = new TestClass();
  $expected = new TestClassTester($object);
  $t->ok(LimeTester::create($object) == $expected, 'The correct object was created');


// @Test: register() registers a new tester for a given interface

  LimeTester::register('TestInterface', 'TestInterfaceTester');
  $object = new TestInterfaceClass();
  $expected = new TestInterfaceTester($object);
  $t->ok(LimeTester::create($object) == $expected, 'The correct object was created');


// @Test: unregister() removes any custom testers for a given class

  LimeTester::register('TestClass', 'TestClassTester');
  LimeTester::unregister('TestClass');
  $object = new TestClass();
  $expected = new LimeTesterObject($object);
  $t->ok(LimeTester::create($object) == $expected, 'The correct object was created');


// @Test: create() creates a tester also for subclasses of a registered tester

  LimeTester::register('TestClass', 'TestClassTester');
  $object = new TestSubClass();
  $expected = new TestClassTester($object);
  $t->ok(LimeTester::create($object) == $expected, 'The correct object was created');


// @Test: register() throws an exception if the tester class does not exist

  $t->expect('InvalidArgumentException');
  LimeTester::register('TestClass', 'FoobarTester');


// @Test: register() throws an exception if the tester does not implement LimeTesterInterface

  $t->expect('InvalidArgumentException');
  LimeTester::register('TestClass', 'InvalidTester');

