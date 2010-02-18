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

$t = new LimeTest(6);


// @Test: greaterThan() throws an exception if the given value is equal

  // fixtures
  $actual = new LimeTesterInteger(1);
  $expected = new LimeTesterInteger(1);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->greaterThan($expected);


// @Test: greaterThan() throws an exception if the given value is greater

  // fixtures
  $actual = new LimeTesterInteger(1);
  $expected = new LimeTesterInteger(2);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->greaterThan($expected);


// @Test: greaterThanEqual() throws an exception if the given value is greater

  // fixtures
  $actual = new LimeTesterInteger(1);
  $expected = new LimeTesterInteger(2);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->greaterThanEqual($expected);


// @Test: lessThanEqual() throws an exception if the given value is smaller

  // fixtures
  $actual = new LimeTesterInteger(2);
  $expected = new LimeTesterInteger(1);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->lessThanEqual($expected);


// @Test: lessThan() throws an exception if the given value is equal

  // fixtures
  $actual = new LimeTesterInteger(1);
  $expected = new LimeTesterInteger(1);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->lessThan($expected);


// @Test: lessThan() throws an exception if the given value is smaller

  // fixtures
  $actual = new LimeTesterInteger(2);
  $expected = new LimeTesterInteger(1);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->lessThan($expected);