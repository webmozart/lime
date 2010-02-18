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

$t = new LimeTest(2);

// @Test
$t->expect('RuntimeException');
echo "Test 1\n";
throw new RuntimeException();

// @Test
$t->expect('LogicException');
echo "Test 2\n";
// should not result in "Got: RuntimeException"!
