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

$t = new LimeTest(3);

// @Test
$t->expect(new RuntimeException("Foobar"));
echo "Test 1\n";
throw new RuntimeException("Foobar");

// @Test
$t->expect(new RuntimeException("Foobar", 1));
echo "Test 2\n";
throw new RuntimeException("Foobar", 0);

// @Test
$t->expect(new RuntimeException("Foobar", 1));
echo "Test 3\n";
throw new RuntimeException("Foobar", 1);
