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

class TestClassWithToString
{
  public function __toString()
  {
    return 'foobar';
  }
}

$t = new LimeTest(3);


// @Test: __toString() returns the string in quotes

  $actual = new LimeTesterString('a\d');
  $t->is($actual->__toString(), "'a\d'", 'The string in quotes is returned');


// @Test: like() throws an exception if the regular expression does not match

  // fixtures
  $actual = new LimeTesterString('a');
  $expected = new LimeTesterString('/\d/');
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->like($expected);


// @Test: like() throws no exception if the regular expression does match

  // fixtures
  $actual = new LimeTesterString('1');
  $expected = new LimeTesterString('/\d/');
  // test
  $actual->like($expected);


// @Test: unlike() throws an exception if the regular expression does match

  // fixtures
  $actual = new LimeTesterString('1');
  $expected = new LimeTesterString('/\d/');
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->unlike($expected);


// @Test: unlike() throws no exception if the regular expression does not match

  // fixtures
  $actual = new LimeTesterString('a');
  $expected = new LimeTesterString('/\d/');
  // test
  $actual->unlike($expected);


