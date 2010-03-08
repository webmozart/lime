Introduction
============

Lime is a robust testing framework written in PHP 5. It is easy to learn and
gives you all you need to create automated tests for your code.
 
Automated testing is an essential part of modern application development.
It allows you to make changes to existing code without introducing regressions.
Given this flexibility, you can quickly react to changing needs of your client
and introduce new features without breaking old functionality.

Unit Tests
----------

It is a good practice to break your system down into small units that can be
tested independently. These tests are called *Unit Tests*.

One common misconception is that developers are responsible for developing
and testers for testing. This is wrong. It is the responibility of the tester to
verify whether the system respects the client's requirements, bears any 
conceptional problems and to set up integration tests. But it is *your*
responsbility to produce working and tested code.

Why Lime?
---------

Lime was first developed by [Fabien Potencier] [1] as light-weight testing 
library for symfony 1.x. It was based on the Perl library *Test::More*
and aimed to produce very concise and readable test code.

In 2009, development of the successor Lime 2 was started by 
[Bernhard Schussek] [2]. The whole code base was overhauled to build a 
stable base for the newly introduced features. Nevertheless, Lime 2 always 
respected its original goals.

Certainly you have heard of [PHPUnit] [3], the excellent, de-facto standard
testing library for PHP. So what are the reasons to use Lime 2 instead?

### Usability

Testing should be fun. If testing is tedious, we don't write tests. Lime 2
provides a clear and concise API that results in very readable tests.
Error messages are tuned to give you exactly the information that you need
to fix the test. 

### Speed

Lime 2 is fast. It is fast to write, because its API is easy to remember. It
is fast to execute because it supports parallel execution of tests on multiple
CPU cores.

### Mocking and Stubbing

Lime 2 provides one of the best mocking libraries available for PHP today.
You can easily generate and configure fake objects to replace test dependencies
and increase test isolation. 

Installation
------------

You can download Lime 2 as [tar archive] [4] or a [zip file] [5].

Alternatively, you can clone the official Lime 2 Git repository:

    $ git clone git://github.com/bschussek/lime.git Lime2
    
### Prerequisites

Lime requires **PHP >= 5.2.6**.
    
### Global Installation

If you want to use one single copy of Lime 2 for all of your projects, you need
to put it in a central location on your computer. Then you need to make the
*lime* binary globally accessible.

On *nix:

    $ mv Lime2 /usr/share/php/Lime2
    $ ln -s /usr/share/php/Lime2/lib/lime /usr/bin/lime
    
On Windows:

1.  Move the `Lime2` directory to a location of your choice (e.g.
    `C:\Program Files\PHP\Lime2`).
    
2.  Copy the `lime.bat` from `Lime2\data\bin` to `C:\Windows\system32`

3.  Open the new `lime.bat` with a text editor. Search for the following line

        set SCRIPT_DIR=C:\Program Files\PHP\Lime2
        
    and change it to point to where you have moved the `Lime2` directory.
    
On both:
    
You can then initialize a new project to use Lime. To do so, open the project
directory in your terminal and type the following command:

    $ lime --init
    
> **NOTE**
> The examples in the following files assume that Lime is installed locally.
> Thus you will find a lot of commands written like this:
> 
>     $ php lime ...
> 
> When Lime is installed globally, you can leave away the "php" in these commands.

### Local Installation

You can also bundle Lime 2 with your project. This is the recommended way,
because it makes sure that all developers in your project use the exact same
version of Lime. To do so, you should move Lime to a subdirectory (e.g.
`lib/vendor/Lime2`) and then initialize your project.

On *nix:

    $ cd /path/to/project
    $ mkdir -p lib/vendor
    $ mv /path/to/Lime2 lib/vendor/Lime2
    $ php lib/vendor/Lime2/lime --init

On Windows:

    > cd C:\Path\To\Project
    > mkdir lib\vendor
    > move C:\Path\To\Lime2 lib\vendor\Lime2
    > php lib\vendor\Lime2\lime --init
    
Lime is now ready to use.

### Adding Test Files

By default, Lime considers all files in the directory `test/` with the suffix 
`Test.php` as test files. You can find out how to change this behaviour in 
chapter 2.

### Migrating Existing symfony Projects

If you want to move your existing symfony project to Lime 2, you need to adapt
the `lime.config.php` file in your project root. Replace the line

    [php]
    $config->registerDir('test', $lime);
    
by the following snippet

    [php]
    foreach (glob(dirname(__FILE__).'/plugins/Alpin*Plugin') as $directory)
    {
	    $pluginName = basename($directory);
    
	    if (is_dir($directory.'/test/unit'))
	    {
		    $config->registerDir($directory.'/test/unit', $lime, array('unit', $pluginName));
	    }
    
	    if (is_dir($directory.'/test/functional'))
	    {
		    $config->registerDir($directory.'/test/functional', $lime, array('functional', $pluginName));
	    }
    }
    
Furthermore, ensure compatibility with lime 1 by enabling the legacy mode.
Therefore change

    [php]
    $config->setLegacyMode(false);
    
to

    [php]
    $config->setLegacyMode(true);
    
Last but not least, you need to modify the file `lib/test/sfTestFunctionalBase.class.php`
and `lib/test/sfTestBrowser.class.php` in the symfony source code. From both
files remove the line

    [php]
    require_once(dirname(__FILE__).'/../vendor/lime/lime.php');
    
Now you can run your tests using the following commands:

    $ php lime --test=MyClass
    $ php lime unit
    $ php lime functional
    $ php lime MyPlugin
    $ php lime unit MyPlugin
    ...
    
> **TIP**
> You will learn more about the command-line interface and how to launch test
> suites in chapter 2.

Support
-------

Support questions and enhancements can be discussed on the [mailing-list] [6].

If you find a bug, you can create a ticket in the [GitHub issue tracker] [7].

License
-------

Lime 2 is licensed under the *MIT license*:

> Copyright (c) 2004-2010 Bernhard Schussek, Fabien Potencier
>
> Permission is hereby granted, free of charge, to any person obtaining a copy
> of this software and associated documentation files (the "Software"), to deal
> in the Software without restriction, including without limitation the rights
> to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
> copies of the Software, and to permit persons to whom the Software is furnished
> to do so, subject to the following conditions:
>
> The above copyright notice and this permission notice shall be included in all
> copies or substantial portions of the Software.
>
> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
> IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
> FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
> AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
> LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
> OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
> THE SOFTWARE.


  [1]: http://fabien.potencier.org
  [2]: http://webmozarts.com
  [3]: http://www.phpunit.de
  [4]: http://github.com/bschussek/lime/zipball/master
  [5]: http://github.com/bschussek/lime/tarball/master
  [6]: http://groups.google.com/group/lime-user
  [7]: http://github.com/bschussek/lime/issues