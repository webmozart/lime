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

class TestClassDefinition
{
  public function testMethodDefinition()
  {
    function testNestedFunctionDefinition() {}

    // test whether $this is ignored
    $this->__toString();
  }
}

class TestClassDefinitionInOneLine {}

interface TestInterfaceDefinition {}

abstract class TestAbstractClassDefinition {}

abstract class TestAbstractClassWithMethod {
  abstract public function testMethod();
}

class TestExtendingClassDefinition extends TestClassDefinition {}

class TestExtendingAbstractClassDefinition extends TestAbstractClassDefinition {}

class TestImplementingClassDefinition implements TestInterfaceDefinition {}

class TestExtendingAndImplementingClassDefinition extends TestClassDefinition implements TestInterfaceDefinition {}

$t = new LimeTest(0);

// @Test
try
{
  throw new Exception();
} catch (Exception $e)
{
  echo "Try is not matched\n";
}

// @Test
if (false)
{
}
else
{
  echo "If is not matched\n";
}

// @Test
// instantiate all classes to see whether they are known to PHP
$class = new TestClassDefinition();
$class = new TestClassDefinitionInOneLine();
$class = new TestExtendingClassDefinition();
$class = new TestExtendingAbstractClassDefinition();
$class = new TestImplementingClassDefinition();
$class = new TestExtendingAndImplementingClassDefinition();
