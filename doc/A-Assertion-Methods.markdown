Appendix A: Assertion Methods
=============================

The following table lists all assertion methods available in Lime 2. Many
assertion methods are both available in the `LimeTest` object as well as in
stubs/mocks when testing method parameters. These method signatures are listed 
in the first and the second column.

> **NOTE**
> All assertion methods accept a last parameter `$message`, which is not
> included in the table for clarity.

The third column lists the data types upon which the assertion is defined by
default.

 `LimeTest`                              | Stub/Mock parameters          | Data types                 | Passes if...
 --------------------------------------- | ----------------------------- | -------------------------- | ---------------------------------------------------
 `pass()`                                | n/a                           | n/a                        | Always
 `fail()`                                | n/a                           | n/a                        | Never
 `ok($condition)`                        | n/a                           | `bool`                     | `$condition == true`
 `is($actual, $expected)`                | `is($expected)`               | all                        | `$actual == $expected`
 `isnt($actual, $expected)`              | `isnt($expected)`             | all                        | `$actual != $expected`
 `same($actual, $expected)`              | `same($expected)`             | all                        | `$actual === $expected`
 `isntSame($actual, $expected)`          | `isntSame($expected)`         | all                        | `$actual !== $expected`
 `like($value, $pattern)`                | `like($expected)`             | `string`                   | the string matches the regular expression
 `unlike($value, $pattern)`              | `unlike($expected)`           | `string`                   | the string does *not* match the regular expression
 `lessThan($actual, $expected)`          | `lessThan($expected)`         | `int`, `double`, `string`  | `$actual < $expected `
 `lessThanEqual($actual, $expected)`     | `lessThanEqual($expected)`    | `int`, `double`, `string`  | `$actual <= $expected `
 `greaterThan($actual, $expected)`       | `greaterThan($expected)`      | `int`, `double`, `string`  | `$actual > $expected `
 `greaterThanEqual($actual, $expected)`  | `greaterThanEqual($expected)` | `int`, `double`, `string`  | `$actual >= $expected `
 `contains($haystack, $needle)`          | `contains($needle)`           | `array`                    | the value `$needle` exists in `$haystack`
 `containsNot($haystack, $needle)`       | `containsNot($needle)`        | `array`                    | the value `$needle` does *not* exist in `$haystack`

Overriding Assertions
---------------------

You can override constraints for your own data types or override the existing 
implementations. A common use case is when you want to compare two objects
that have properties irrelevant for their equality.

A good example are Active Record classes that store a reference to their 
Data Acccess Object (DAO) to lazy load their properties.

    [php]
    class User
    {
      public $id;
      public $name;
      public $dao;
    }
    
Two `User` objects might have the same `$id` and `$name` properties, while one
stores a reference to the DAO and the other doesn't. Should these two objects
be considered equal? In most cases the answer is yes.

    [php]
    $t = new LimeTest();
    
    $user1 = new User();
    $user1->id = 1;
    $user1->name = 'Bernhard';
    
    $user2 = new User();
    $user2->id = 1;
    $user2->name = 'Bernhard';
    $user2->dao = new UserDAO();
    
    $t->is($user1, $user2); // => fails

Unfortunately for you, Lime compares all properties of the objects and considers
them unequal. Fortunately you can override the assertion `is()` for your own
data type by creating a new tester.

### Creating Custom Testers

First of all you need to understand how testers work. Testers *wrap tested
values*. When you run a binary (with two parameters) assertion, both parameters
of the assertion are wrapped into testers on which the assertion is executed.

    [php]
    $tester1 = new LimeTesterInteger(10);
    $tester2 = new LimeTesterInteger(20);
    
    $tester1->is($tester2);
    // => throws a LimeAssertionFailedException
    
Now, all testers must implement the interface `LimeTesterInterface`.

    [php]
    interface LimeTesterInterface
    {
      public function is(LimeTesterInterface $expected);
    
      public function isnt(LimeTesterInterface $expected);
      
      ...
    }
    
The interface declares all binary assertion methods of the above table. The 
first and only argument of the assertion methods is a different tester object.
If the assertion fails, a `LimeAssertionFailedException` with information about
the error should be thrown. Otherwise nothing happens.

### Predefined Testers

Lime implements testers for the basic PHP data types for you.

 Tester class           | Data types     | Remark
 ---------------------- | -------------- | -------------------------------------
 `LimeTester`           | n/a            | Base class for all testers
 `LimeTesterScalar`     | `null`, `bool` | Extends `LimeTester`
 `LimeTesterInteger`    | `int`          | Extends `LimeTesterScalar`
 `LimeTesterDouble`     | `double`       | Extends `LimeTesterScalar`
 `LimeTesterString`     | `string`       | Extends `LimeTesterScalar`
 `LimeTesterArray`      | `array`        | Extends `LimeTester`
 `LimeTesterObject`     | `object`       | Extends `LimeTesterArray`
 `LimeTesterException`  | `Exception`    | Extends `LimeTesterObject`
 `LimeTesterResource`   | `resource`     | Extends `LimeTester`

To override an assertion for a custom data type (e.g. a class or interface), the
simplest way is to extend one of the predefined testers.

    [php]
    class UserTester extends LimeTesterObject
    {
      public function __construct(User $user)
      {
        parent::__construct($user);
        
        unset($this->value['dao']);
      }
    }
    
Our custom `UserTester` now extends the existing object tester. The object
tester (as all other testers) stores its value in the property `$value`, with
the exception that the object properties are converted to an associative array.
By unsetting some properties in the array you make sure that these properties
are ignored in all further assertion operations.

### Registering Custom Testers

The last thing you need to do is to register your tester for your data type.

    [php]
    LimeTester::register('User', 'UserTester');
    
And finally, the complete test with our custom tester.

    [php]
    LimeTester::register('User', 'UserTester');
    $t = new LimeTest();
    
    $user1 = new User();
    $user1->id = 1;
    $user1->name = 'Bernhard';
    
    $user2 = new User();
    $user2->id = 1;
    $user2->name = 'Bernhard';
    $user2->dao = new UserDAO();
    
    $t->is($user1, $user2); // => passes!

> **NOTE**
> One last remark before you start implementing your own crazy assertions.
> The assertion methods in the testers are *not commutative*! I.e., if you 
> override the tester `is()` for class `User`, the custom implementation will
> only be used when comparing ``User == other data type``, but not when
> comparing ``other data type == User``.
>
> That doesn't matter as long as you only compare `User` objects with other
> `User` objects. As soon as you start to compare two different data types though
> you need to override *both* testers for *both* data types.