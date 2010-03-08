The Command-Line
================

Lime offers a very extensive command-line interface (CLI) for launching single
tests or test suites. You have already learned how to launch a single test:

    $ php lime --test=Class
    
Lime uses the information provided in `lime.config.php` to find the test for
this class.

Test Registration
-----------------

Open the `lime.config.php` in your project root and search for the following
lines:

    [php]
    $lime = LimeExecutable::php('lime', 'raw', array('--output' => 'raw'));

    $config->registerDir('test', $lime);
    
In the last line, all files in the directory `test/` that have the suffix
`Test.php` are registered to be executed with the `lime` executable.

> **NOTE**
> You can also hook tests from other sources like PHPT into your Lime test
> suite. For more information refer to Appendix B.

The following registration methods are available:

 Method                                | Description
 ------------------------------------- | ---------------------------------------
 `registerDir($directory, $exec)`      | Registers all files in `$directory` with the suffix `Test.php`
 `registerGlob($glob, $exec)`          | Registers all files matching the glob expression
 `registerCallback($callback, $exec)`  | Registers all files returned by the callback

> **TIP**
> You can change the default prefix of test files by modifying the following
> line:
>
>     [php]
>     $config->setSuffix('Test.php');

Test Labels
-----------

All `register*()` methods support an optional third parameter in which you can
specify one or more labels for the matching test files.

    [php]
    $config->registerDir('test/unit', $lime, 'unit');
    $config->registerDir('test/functional', $lime, 'functional');
    $config->registerDir('MyPackage/test/unit', $lime, array('MyPackage', 'unit'));
    
These labels can be used to dynamically execute subsets of your test suite, as
you will learn in the following section.

Running Tests
-------------

You can run all tests in your test suite by simply calling

    $ php lime
    
--IMAGE--

The output includes the labels, the test names, their status ("ok" or "not ok")
and more information about the failures in case a test failed.

If you want to run a specific test, add the parameter `--test` with the name
of the test.

    $ php lime --test=Class
    
You can also run only the tests with a given label.

    $ php lime mylabel
    
The real power of test labels lies in combining labels. By applying set 
operations you can exactly specify which tests you want to run.

### Intersection

    $ php lime unit MyPackage
    
Runs only tests labeled with "unit" and "MyPackage" at the same time.

### Summation

    $ php lime unit +integration
    
Runs all tests labeled with "unit" and all tests labeled with "integration".

### Difference

    $ php lime unit -slow
    
Runs all tests labeled with "unit" but not with "slow".

### Associativity

All set operations are left-associative (i.e. evaluated from left to right).
You can combine as many operations as you want. The following call, for example,
is perfectly possible.

    $ php lime unit +integration -slow
    
First, Lime builds the summation of "unit" and "integration". From the files
in this set, all files with the label "slow" are removed. The resulting set
of tests is executed.