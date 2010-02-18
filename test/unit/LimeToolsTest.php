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

include_once dirname(__FILE__).'/../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(1);


// @Test: indent() indents every line of the string

  $string = <<<EOF
First line
Second line
   Third line
EOF;
  $expected = <<<EOF
   First line
   Second line
      Third line
EOF;
  $t->is(LimeTools::indent($string, 3), $expected, 'All lines except the first one were indented');