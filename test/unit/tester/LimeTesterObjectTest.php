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

class TestClass
{
  private $a;
  protected $b = 1;
  public $c = 2;

  public function __construct($a = 0)
  {
    $this->a = $a;
  }
}

class TestBook
{
  // the order of properties is crucial!
  public $author = null;
  private $title = '';

  public function __construct($title)
  {
    $this->title = $title;
  }
}

class TestAuthor
{
  // the order of properties is crucial!
  public $books = array();
  private $name = '';

  public function __construct($name)
  {
    $this->name = $name;
  }
}

LimeAnnotationSupport::enable();

$t = new LimeTest(5);


// @Test: is() throws an exception if values don't match

  // fixtures
  $actual = new LimeTesterObject(new TestClass(0));
  $expected = new LimeTesterObject(new TestClass(1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->is($expected);


// @Test: is() throws no exception if values match

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterObject(new TestClass());
  // test
  $actual->is($expected);


// @Test: is() is able to deal with cyclic dependencies

  // fixtures
  $book1 = new TestBook('Thud');
  $book1->author = new TestAuthor('Terry Pratchett');
  $book1->author->books[] = $book1;
  $book2 = new TestBook('Thud');
  $book2->author = new TestAuthor('Terry Pratchett');
  $book2->author->books[] = $book2;
  $actual = new LimeTesterObject($book1);
  $expected = new LimeTesterObject($book2);
  // test
  $actual->is($expected);


// @Test: is() throws an exception if cyclic dependencies contain differences

  // fixtures
  $book1 = new TestBook('Thud');
  $book1->author = new TestAuthor('Terry Pratchett');
  $book1->author->books[] = $book1;
  $book2 = new TestBook('Thud');
  $book2->author = new TestAuthor('Terry Pratch');
  $book2->author->books[] = $book2;
  $actual = new LimeTesterObject($book1);
  $expected = new LimeTesterObject($book2);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->is($expected);


// @Test: same() throws an exception if objects are not the same

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterObject(new TestClass());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->same($expected);


// @Test: same() throws no exception if objects are the same

  // fixtures
  $object = new TestClass();
  $actual = new LimeTesterObject($object);
  $expected = new LimeTesterObject($object);
  // test
  $actual->same($expected);


// @Test: isnt() throws an exception if the objects are equal

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterObject(new TestClass());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->isnt($expected);


// @Test: isnt() is able to deal with cyclic dependencies

  // fixtures
  $book1 = new TestBook('Thud');
  $book1->author = new TestAuthor('Terry Pratchett');
  $book1->author->books[] = $book1;
  $book2 = new TestBook('Thud');
  $book2->author = new TestAuthor('Terry Pratch');
  $book2->author->books[] = $book2;
  $actual = new LimeTesterObject($book1);
  $expected = new LimeTesterObject($book2);
  // test
  $actual->isnt($expected);


// @Test: isntSame() throws an exception if the objects are identical

  // fixtures
  $object = new TestClass();
  $actual = new LimeTesterObject($object);
  $expected = new LimeTesterObject($object);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->isntSame($expected);


// @Test: isntSame() throws no exception if the objects are equal

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterObject(new TestClass());
  // test
  $actual->isntSame($expected);

