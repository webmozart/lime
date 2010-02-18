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

include dirname(__FILE__).'/../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(3);


// @BeforeAll

  $file1 = $t->stub('LimeFile');
  $file1->getPath()->returns('test1.txt');
  $file1->replay();
  $file2 = $t->stub('LimeFile');
  $file2->getPath()->returns('test2.txt');
  $file2->replay();
  $file3 = $t->stub('LimeFile');
  $file3->getPath()->returns('test3.txt');
  $file3->replay();


// @Before

  $label1 = new LimeLabel();
  $label1->addFile($file1);
  $label1->addFile($file2);
  $label2 = new LimeLabel();
  $label2->addFile($file1);
  $label2->addFile($file3);


// @Test: intersect() returns the intersection of two labels

  $expected = new LimeLabel();
  $expected->addFile($file1);
  $actual = $label1->intersect($label2);
  $t->is($actual, $expected, 'The intersection is correct');


// @Test: add() returns the sum of two labels

  $expected = new LimeLabel();
  $expected->addFile($file1);
  $expected->addFile($file2);
  $expected->addFile($file3);
  $actual = $label1->add($label2);
  $t->is($actual, $expected, 'The sum is correct');


// @Test: subtract() returns the first label without the second

  $expected = new LimeLabel();
  $expected->addFile($file2);
  $actual = $label1->subtract($label2);
  $t->is($actual, $expected, 'The subtraction is correct');