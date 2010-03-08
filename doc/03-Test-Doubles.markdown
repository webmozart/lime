Test Doubles
============

Good code is decoupled, and decoupled code is easily testable. Let's look at an
example.

    [php]
    class Product
    {
      public function process()
      {
        if (ProductDAO::getInstance()->exists($this->id))
        {
          // do something
        }
      }
    }
    
How can you test that `process()` relies on the `ProductDAO` foobar? You need to
initialize the singleton instance, which might be an expensive operation. 
Furthermore, resetting the singleton instance between tests can be difficult, 
which reduces test isolation and may introduce unexpected test behaviour. How 
can we solve this problem?

The answer is called Dependency Injection. Instead of relying on the singleton,
you should inject the `ProductDAO` (the "dependency") through a constructor 
argument, via a setter or directly when executing the method.

    [php]
    class Product
    {
      public function process(ProductDAO $dao)
      {
        if ($dao->exists($this->id))
        {
          // do something
        }
      }
    }
    
Now you can easily create a new `ProductDAO` instance for every test and inject
it into the `process()` method.

What if the DAO itself depends on other objects, such as a session storage or
a database? What if `exists()` is a very slow operation? Your tests will
become slow and you won't run them as often as you should. But again there is a 
very simple solution: Replace the `ProductDAO` with a fake object, a so-called
*test double*.

    [php]
    class FakeProductDAO extends ProductDAO
    {
      protected $exists;
      
      public function __construct($exists)
      {
        $this->exists = $exists;
      }
      
      public function exists(Product $product)
      {
        return $this->exists;
      }
    }
    
The `FakeProductDAO` always returns a fixed, preconfigured value when `exists()`
is called. Now you can easily test the `Product`.

    [php]
    $t = new LimeTest();
    
    // @Before
    
      $product = new Product();
    
    // @Test: process() does something if the product exists
    
      $dao = new FakeProductDao(true);
      $product->process($dao);
      ...
      
    // @Test: process() does nothing if the product does not exist
    
      $dao = new FakeProductDao(false);
      $product->process($dao);
      ...

Stubs
-----

Lime 2 provides support to automatically generate test doubles for you. The
first type of test doubles are *stubs*.

You can generate a stub with the method `stub()` on `LimeTest`.

    [php]
    $dao = $t->stub('ProductDAO');
    
> **NOTE**
> You can create stubs for existing classes or interfaces, but also for
> non-existing ones! This allows you to explore how the stubbed class should
> behave before even implementing it.

Like the `FakeProductDAO`, the stub simply replaces a dependency of the tested
class. Method calls are either ignored or can be preconfigured to return a
value, throw an exception and more.

When the stub is created, it is in *record mode*. To configure a method, you 
first call the method on the stub with the expected parameters. Then you use
the mocks fluent interface to append several method calls (the *modifiers*),
which configure the behaviour of the call.

When the stub configuration is complete, you need to call `replay()` to turn
the stub into *replay mode*. In this mode, the stub will behave exactly as
configured. 

### Returning Fixed Values

The modifier `returns()` configures a method to always return a fixed value.

    [php]
    $dao->exists($product)->returns(true);
    $dao->replay();
    
    var_dump($dao->exists($product));
    
    // Result:
    bool(true)
    
### Throwing Exceptions

With the modifier `throws()` you can configure a method to always throw an
exception of a given class.

    [php]
    $dao->exists($product)->throws('LogicException');
    $dao->replay();
    
    $dao->exists($product);
    
    // Result:
    Uncaught exception: LogicException
    
You can also pass an exception object that will be thrown.

    [php]
    $dao->exists($product)->throws(new LogicException('Error!'));
    $dao->replay();
    
    $dao->exists($product);
    
    // Result:
    Uncaught exception: LogicException
    
### Parameter Matching
    
You can configure different behaviours for different method parameters.

    [php]
    $dao->exists($product1)->returns(true);
    $dao->exists($product2)->returns(false);
    $dao->replay();
    
    var_dump($dao->exists($product1));
    var_dump($dao->exists($product2));
    
    // Result:
    bool(true)
    bool(false)

By default, a fake method will only be activated when you pass the exact same
parameters as configured.

    [php]
    $dao->exists($product)->returns(true);
    $dao->replay();
    
    var_dump($dao->exists(new Product()));
    
    // Result:
    NULL
    
If you want the fake method call to react *always*, you need to pass the name
of the fake method to `method()`:

    [php]
    $dao->method('exists')->returns(true);
    $dao->replay();
    
    var_dump($dao->exists(new Product()));
    
    // Result:
    bool(true)
    
> **TIP**
> What if `method()` or `replay()` exists in your own class? You will learn
> how to configure the stub in this case at the end of the chapter.
    
You can also test specific method parameters:

    [php]
    $dao->method('exists')
        ->parameter(2)->is(true)
        ->returns(true);
    $dao->replay();
    
    var_dump($dao->exists($product));
    var_dump($dao->exists($product, true));
    
    // Result:
    NULL
    bool(true)
    
Lime supports many different parameter assertion methods.

 Method              | Description
 ------------------- | ---------------------------------------------------------
 `is($value)`        | Compares with a value and passes if it is equal (`==`)
 `isnt($value)`      | Compares with a value and passes if it is not equal
 `same($value)`      | Compares with a value and passes if it is identical (`===`)
 `like($regexp)`     | Tests a the parameter against a regular expression
 `unlike($regexp)`   | Checks that the parameter doesn't match a regular expression

> **TIP**
> You can see the complete list of assertion methods in Appendix A.

### Type-safe Parameter Matching

If you configure the parameter assertions manually, comparisons will be weak
by default.

    [php]
    $dao->exists($product1)->returns(true);
    $dao->replay();
    
    var_dump($dao->exists($product1));
    var_dump($dao->exists(clone $product1));
    
    // Result:
    bool(true)
    bool(true)
    
You can override this behaviour by calling the modifier `strict()`.

    [php]
    $dao->exists($product1)->strict()->returns(true);
    $dao->replay();
    
    var_dump($dao->exists($product1));
    var_dump($dao->exists(clone $product1));
    
    // Result:
    bool(true)
    bool(false)

> **TIP**
> This is identical to configuring the assertion `same()` for every parameter.
    
### Callbacks

The modifier `callback()` configures the method to forward its call to a
valid callable. The result of the callable will be returned by the method.

    [php]
    function test_exists(Product $product)
    {
      return $product->id >= 2;
    }
    
    $dao->method('exists')->callback('test_exists');
    $dao->replay();
    
    var_dump($dao->exists($product));
    $product->id = 2;
    var_dump($dao->exists($product));

    // Result:
    NULL
    bool(true)
    
Mocks
-----

In some situations you want to test the communication between two classes. Look
at the following example.

    [php]
    class Product
    {
      public function save(ProductDAO $dao)
      {
        $dao->insert($this);
      }
    }

How you can whether `insert()` was called exactly once and with the correct
parameter, namely the product itself? With *mock objects*.

    [php]
    $dao = $t->mock('ProductDAO');

Mocks are stubs with special capabilities.

1.  They allow you to configure which methods *should be* called
2.  They can test *how often* a method was called
3.  They can test the order of method calls
4.  Unlike stubs, mocks throw exceptions when non-configured methods are invoked

The following sections explore these capabilities in more detail.

### Expecting Method Calls

Method calls are configured in the same way as on stubs. The big difference is
that a test will fail if the method was not called.

    [php]
    // @Test: save() inserts the product in the DAO

      $product = new Product();    
      $dao = $t->mock('ProductDAO');
      $dao->insert($product);
      $dao->replay();
      
      $product->save($dao);

This test will fail if `insert()` was not called or with the wrong parameter.

### Counting Method Calls

By default, methods are expected to be called exactly once. Otherwise, the mock
will throw an exception. If you want to configure the method to be called more
than once, you can use one of the different count modifiers.

 Modifier              | Description
 --------------------- | ---------------------------------------------------------
 `any()`               | Passes if called 0 or more times (default for stubs)
 `once()`              | Passes if called exactly once (default for mocks)
 `atLeastOnce()`       | Passes if called 1 or more times
 `never()`             | Passes if called never
 `times($number)`      | Passes if called exactly `$number` times
 `between($min, $max)` | Passes if the number of calls is `>= $min` and `<= $max`
 
### Testing the Order of Method Calls

Mocks and stubs usually don't care in which order the configured methods are
called. In some scenarios, for instance when testing the communication with a
web service, it is important to verify that methods are invoked in the right
order.

To enable order testing, set the option "strict" to `true` when creating the
mock.

    [php]
    // @Test: sendMessage() sends a message with the given webservice
    
      $service = $t->mock('WebService', array('strict' => true));
      $service->authenticate('user', 'password');
      $service->send('message');
      $service->replay();
      $user = new User();
      
      $user->sendMessage($service, 'message');

The test will fail unless the methods `authenticate()` and `send()` are called
in exactly this order.

### Ignoring Unexpected Method Calls

As described before, mocks throw an exception if a method is called that was not 
configured to be called. You can override this behaviour by setting the option
"nice" to `true` when creating the mock.

    [php]
    // @Test: sendMessage() sends a message with the given webservice
    
      $service = $t->mock('WebService', array('nice' => true));
      $service->send('message');
      $service->replay();
      $user = new User();
      
      $user->sendMessage($service, 'message');
      
Any other calls than `send()` on the `WebService` mock are simply ignored.

Partial Stubs/Mocks
-------------------

Stubs and mocks generated by Lime override *all* existing methods in the 
stubbed/mocked class. As a result, you are completely independent from the
real implementation.

Sometimes though it is necessary to use the real implementation of some methods,
while faking other methods. A common use case for this are abstract classes.

    [php]
    abstract class Currency
    {
        public function amount($dollars)
        {
            return $this->getConversionRate() * $dollars;
        }
    
        protected abstract function getConversionRate();
    }

Often it is sufficient to replace only the abstract methods of an abstract
class with fake implementations. Lime calls such objects *partial stubs* and 
*partial mocks*, because they reuse a part of the real implementation. They can 
be generated using the methods `extendStub()` and `extendMock()`.

    [php]
    $stub = $t->extendStub('AbstractClass');
    $mock = $t->extendMock('AbstractClass');
    
> **TIP**
> The methods begin with "extend" because Lime simply creates subclasses in
> which fake method implementations are added.

Lime will call all methods in the parent class that don't match one of the
configured methods.

    [php]
    // @Test: getPrice() calculates the converted product price
    
      $currency = $t->extendStub('Currency');
      $currency->getConversionRate()->returns(2);
      $currency->replay();
      $product = new Product(100);
      
      $t->is($product->getPrice(), 200);
      
Partial mocks can help you to test how often an abstract method was called,
whether it was called with the right parameters etc.

     [php]
     // @Test: amount() accepts an explicit conversion rate
     
       $currency = $t->extendMock('Currency');
       $currency->getConversionRate()->never();
       $currency->replay();
       
       $t->is($currency->amount(100, 2), 200);
       
     // @Test: amount() uses the stored conversion rate if none is given
     
       $currency = $t->extendMock('Currency');
       $currency->getConversionRate()->returns(3);
       $currency->replay();
       
       $t->is($currency->amount(100), 300);
