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

$t = new LimeTest(12);


// @Test: is() throws an exception if keys are missing

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->is($expected);


// @Test: is() throws an exception if keys are unexpected

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->is($expected);


// @Test: is() throws an exception if values don't match

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 2));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->is($expected);


// @Test: is() throws no exception if the order is different

  // fixtures
  $actual = new LimeTesterArray(array('a' => 1, 'b' => 2));
  $expected = new LimeTesterArray(array('b' => 2, 'a' => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->same($expected);


// @Test: is() throws no exception if values match

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $actual->is($expected);


// @Test: is() throws no exception if values match

  // fixtures
  $actual = new LimeTesterArray(array(0 => new LimeError("message", "file", 11)));
  $expected = new LimeTesterArray(array(0 => new LimeError("message", "file", 11)));
  // test
  $actual->is($expected);


// @Test: isnt() throws an exception if the arrays are equal

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->isnt($expected);


// @Test: isnt() throws no exception if the arrays differ

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1, 1 => 2));
  $expected = new LimeTesterArray(array(0 => 1, 1 => 3));
  // test
  $actual->isnt($expected);


// @Test: same() throws an exception if keys are missing

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->same($expected);


// @Test: same() throws an exception if keys are unexpected

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->same($expected);


// @Test: same() throws an exception if types are different

  // fixtures
  $actual = new LimeTesterArray(array(1));
  $expected = new LimeTesterArray(array('1'));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->same($expected);


// @Test: same() throws an exception if the order is different

  // fixtures
  $actual = new LimeTesterArray(array('a' => 1, 'b' => 2));
  $expected = new LimeTesterArray(array('b' => 2, 'a' => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->same($expected);


// @Test: same() throws no exception if values match

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $actual->same($expected);


// @Test: isntSame() throws an exception if the arrays are equal

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->isntSame($expected);


// @Test: isntSame() throws no exception if the types differ

  // fixtures
  $actual = new LimeTesterArray(array(1));
  $expected = new LimeTesterArray(array('1'));
  // test
  $actual->isntSame($expected);


// @Test: isntSame() throws no exception if the order differs

  // fixtures
  $actual = new LimeTesterArray(array('a' => 1, 'b' => 2));
  $expected = new LimeTesterArray(array('b' => 2, 'a' => 1));
  // test
  $actual->isntSame($expected);


// @Test: contains() throws an exception if a value is not in the array

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = LimeTester::create(0);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->contains($expected);


// @Test: contains() throws no exception if a value is in the array

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = LimeTester::create(1);
  // test
  $actual->contains($expected);


// @Test: containsNot() throws an exception if a value is in the array

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = LimeTester::create(1);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->containsNot($expected);


// @Test: containsNot() throws no exception if a value is not in the array

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = LimeTester::create(0);
  // test
  $actual->containsNot($expected);

