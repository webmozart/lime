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

Lime was first developed by [Fabien Potencier][fabpot] as light-weight testing 
library for symfony 1.x. It was based on the Perl library *Test::More*
and aimed to produce very concise and readable test code.

In 2009, development of the successor Lime 2 was started by 
[Bernhard Schussek][bschussek]. The whole code base was overhauled to build a 
stable base for the newly introduced features. Nevertheless, Lime 2 always 
respected its original goals.

Surely you have heard of [PHPUnit][phpunit], the de-facto standard testing 
library for PHP. Given this big and well-respected library, what are the reasons
to use Lime 2 instead?

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

You can download Lime 2 as [tar archive][tar] or a [zip file][zip].

Alternatively, you can clone the official Lime 2 Git repository:

    $ git clone git://github.com/bschussek/lime.git
    
### Global Installation

If you want to use one single copy of Lime 2 for all of your projects, you need
to put it in a central location on your computer. Then you need to make the
*lime* binary globally accessible.

On Linux:

    $ mv Lime2 /usr/share/php/Lime2
    $ ln -s /usr/share/php/Lime2/lib/lime /usr/bin/lime
    
On Windows:

    > move Lime2 "C:\Program Files\php\Lime2"
    > set path="%PATH%;C:\Program Files\php\Lime2"
    
You can then initialize a new project to use Lime. To do so, open the project
directory in your terminal and type the following command:

    $ lime --init

### Local Installation

You can also bundle Lime 2 with your project. This is the recommended way,
because it makes sure that all developers in your project

### Upgrading Symfony 1.x Projects

Support
-------

Support text

License
-------

See the LICENSE file distributed with the source code.