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

$t = new LimeTest(5);


// @Test: is() throws an exception if the values differ

  // fixtures
  $actual = new LimeTesterScalar('a');
  $expected = new LimeTesterScalar('b');
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->is($expected);


// @Test: is() throws an exception if the values differ and standard comparison succeeds

  // fixtures
  // 0 == 'Foobar' => true!
  $actual = new LimeTesterScalar(0);
  $expected = new LimeTesterScalar('Foobar');
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->is($expected);


// @Test: is() throws no exception if the values are equal, but different types

  // fixtures
  $actual = new LimeTesterScalar('0');
  $expected = new LimeTesterScalar(0);
  // test
  $actual->is($expected);


// @Test: is() throws no exception if both values are NULL

  // fixtures
  $actual = new LimeTesterScalar(null);
  $expected = new LimeTesterScalar(null);
  // test
  $actual->is($expected);


// @Test: same() throws an exception if the values have different types

  // fixtures
  $actual = new LimeTesterScalar('0');
  $expected = new LimeTesterScalar(0);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->same($expected);


// @Test: isnt() throws an exception if values are equal

  // fixtures
  $actual = new LimeTesterScalar(1);
  $expected = new LimeTesterScalar(1);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->isnt($expected);


// @Test: isnt() throws an exception if both values are null

  // fixtures
  $actual = new LimeTesterScalar(null);
  $expected = new LimeTesterScalar(null);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->isnt($expected);


// @Test: isnt() throws no exception if values differ but standard comparison succeeds

  // fixtures
  // 0 == 'Foobar' => true!
  $actual = new LimeTesterScalar(0);
  $expected = new LimeTesterScalar('Foobar');
  // test
  $actual->isnt($expected);


// @Test: isntSame() throws no exception if values are equal but types are different

  // fixtures
  $actual = new LimeTesterScalar('1');
  $expected = new LimeTesterScalar(1);
  // test
  $actual->isntSame($expected);