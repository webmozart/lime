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

class LimeMockInvocationTrace implements IteratorAggregate, Countable
{
  protected
    $invocations = array();

  public function push($invocation)
  {
    array_push($this->invocations, $invocation);
  }

  public function getIterator()
  {
    return new ArrayIterator($this->invocations);
  }

  public function count()
  {
    return count($this->invocations);
  }
}