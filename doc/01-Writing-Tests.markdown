Writing Tests
=============

Tests in Lime are simple, procedural PHP files. The following example shows a
very basic test file.

    [php]
    // test/ArrayTest.php
    $t = new LimeTest();
    
    $array = array('first', 'last');
    $t->is(array_pop($array), 'last');
    $t->is($array, array('first'), 'The last element was removed');
    
Use the `lime` utility to launch the test.

    $ php lime --test=Array
    
--IMAGE--

> **NOTE**
> Windows command line unfortunately cannot highlight test results in red or 
> green color. But if you use Cygwin, you can force symfony to use colors by 
> lime -passing the --color option to the task.

The example shows the basic steps required to write a test file:

1.  Test files generally have the suffix `Test.php`. Tests for a class `Class`
    usually	go into `ClassTest.php`
2.  A `LimeTest` object is required to execute tests
3.  Testing works by calling a method or a function with a set of predefined 
    inputs and then comparing the results with the expected output. This 
    comparison determines whether a test passes or fails.
  
Lime provides several assertion methods to ease comparison.

 Method                        | Description
 ----------------------------- | --------------------------------------------
 `ok($test)`                   | Tests a condition and passes if it is true
 `is($value1, $value2)`        | Compares two values and passes if they are  equal (`==`)
 `isnt($value1, $value2)`      | Compares two values and passes if they are not equal
 `same($value1, $value2)`      | Compares two values and passes if they are  identical (`===`)
 `like($string, $regexp)`      | Tests a string against a regular expression
 `unlike($string, $regexp)`    | Checks that a string doesn't match a regular expression

> **TIP**
> You can see the complete list of assertion methods in Appendix A.

Every assertion method accepts an optional last parameter to describe what the 
assertion is testing. It is a good practice to include this comment if the 
purpose of assertion  is not obvious enough.

Annotations
-----------

Tests should always be executed in isolated environments. To support this need,
Lime 2 supports the concept of **annotations**.

Annotations allow you to group your code into code blocks.

    [php]
    // ArrayTest.php
    $t = new LimeTest();
    
    // @Before
    
      $array = array('first', 'last');
    
    // @Test: array_pop() removes and returns the last element
     
      $t->is(array_pop($array), 'last');
      $t->is($array, array('first'));
    
    // @Test: array_push() adds an element at the end
     
      array_push($array, 'very last');
      $t->is($array, array('first', 'last', 'very last'));
    
Lime annotations follow very simple rules:

1.  A PHP single line comment starting with "@" is an annotation
2.  Annotations can be commented. The comment succeeds the annotation name
    separated by a ":".
3.  Every code between two annotations is executed in a seperate namespace

The following annotations are supported.

 Annotation       | Description
 ---------------- | --------------------------------------------
 `@Test`          | Describes a test case
 `@Before`        | Executed *before every* @Test annotation
 `@After`         | Executed *after every* @Test annotation
 `@BeforeAll`     | Executed *once before all* @Test annotations
 `@AfterAll`      | Executed *once after all* @Test annotations
 
Variables from the global namespace, the `@Before` and the `@BeforeAll`
annotations are also made available in other annotated blocks. All other 
variables are private to their block.

> **NOTE**
> Annotation support is by default enabled in all test files. If you want to
> suppress this behaviour, you can disable annotation support in `lime.config.php`.
> You can also explicitely enable annotation support for single files by
> including the following line at **the very beginning** of the file:
>
>     [php]
>     LimeAnnotationSupport::enable()

Testing Exceptions
------------------

You can easily test whether the code inside an annotation throws an exception.
To do so, call the method `expect()` on `LimeTest` just before the code that
should throw the exception.

    [php]
    // ExceptionTest.php
    $t = new LimeTest();
    
    // @Test: An exception should be thrown
    
      $t->expect('LogicException');
      throw new LogicException('Bail out!');
      
The test will fail if no exception is thrown or if the class of the thrown
exception does not match the given exception class.

If you want to test additional properties of the exception, you can pass a
sample exception object to `expect()`. This object will be compared with the
thrown exception. If the objects do not match, the test will fail.

    [php]
    // ExceptionTest.php
    $t = new LimeTest();
    
    // @Test: An exception with a specific message should be thrown
    
      $t->expect(new LogicException('Bail out!'));
      throw new LogicException('Foobar');
      
The above test will fail because the message of the thrown exception does not
match the message of the expected exception.

Marking TODOs
-------------

If you think of a specific test that should yet be written or a functionality
that should be implemented later, you can write a TODO message in your test
using the method `todo()` on `LimeTest`.

    [php]
    // TodoTest.php
    $t = new LimeTest();
    
    $t->todo('Test whether the method produces rounding errors');
    
Everytime when you execute this test, you will be remembered to implement this
functionality.