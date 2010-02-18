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


function create_lexer()
{
  return new LimeLexerTestVariable();
}


$t = new LimeTest(4);


$t->diag('The first variable that is assigned an instance of LimeTest is detected');

  // fixtures
  $l = create_lexer();
  // test
  $actual = $l->parse(<<<EOF
<?php
\$a = 0;
\$b = new LimeTest();
\$c = "foobar";
\$d = new LimeTest();
EOF
  );
  // assertions
  $t->is($actual, '$b', 'The correct variable was detected');


$t->diag('Assignments in functions are ignored');

  // fixtures
  $l = create_lexer();
  // test
  $actual = $l->parse(<<<EOF
<?php
function test() {
  \$a = new LimeTest();
}
class Test {
  public function test() {
    \$b = new LimeTest();
  }
}
\$c = new LimeTest();
EOF
  );
  // assertions
  $t->is($actual, '$c', 'The correct variable was detected');


$t->diag('Assignments of classes extending LimeTest are detected');

  // fixtures
  $l = create_lexer();
  // test
  $actual = $l->parse(<<<EOF
<?php
\$a = new LimeTestCase();
EOF
  );
  // assertions
  $t->is($actual, '$a', 'The correct variable was detected');


$t->diag('Assignments of unknown classes are ignored');

  // fixtures
  $l = create_lexer();
  // test
  $actual = $l->parse(<<<EOF
<?php
\$a = new Foobar();
EOF
  );
  // assertions
  $t->is($actual, null, 'No variable was detected');