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

class LimeTestTest extends LimeTestCase
{
  protected $colorizer;
  protected $printer;

  public function setUp()
  {
    $this->colorizer = $this->stub('LimeColorizer');
    $this->printer = new LimePrinter($this->colorizer);
  }

  public function tearDown()
  {
    $this->colorizer = null;
    $this->printer = null;
  }

  public function testPrintTextUsingTheGivenStyle()
  {
    // fixtures
    $this->colorizer->colorize('My text', 'RED')->returns('<RED>My text</RED>');
    $this->colorizer->replay();
    // test
    ob_start();
    $this->printer->printText('My text', 'RED');
    $result = ob_get_clean();
    // assertions
    $this->is($result, '<RED>My text</RED>', 'The result was colorized and printed');
  }

  public function testPrintTextFollowedByNewline()
  {
    // fixtures
    $this->colorizer->colorize('My text', 'RED')->returns('<RED>My text</RED>');
    $this->colorizer->replay();
    // test
    ob_start();
    $this->printer->printLine('My text', 'RED');
    $result = ob_get_clean();
    // assertions
    $this->is($result, "<RED>My text</RED>\n", 'The result was colorized and printed');
  }

  public function testPrintTextIn80CharactersWideBox()
  {
    // fixtures
    $paddedText = str_pad('My text', 80, ' ');
    $this->colorizer->colorize($paddedText, 'RED')->returns('<RED>'.$paddedText.'</RED>');
    $this->colorizer->replay();
    // test
    ob_start();
    $this->printer->printBox('My text', 'RED');
    $result = ob_get_clean();
    // assertions
    $this->is($result, '<RED>'.$paddedText."</RED>\n", 'The result was colorized and printed');
  }

  public function testPrintTextIn80CharactersWideBoxWithSpaceAround()
  {
    // fixtures
    $paddedText = str_pad('  My text', 80, ' ');
    $paddedSpace = str_repeat(' ', 80);
    $this->colorizer->colorize($paddedText, 'RED')->returns('<RED>'.$paddedText.'</RED>');
    $this->colorizer->colorize($paddedSpace, 'RED')->returns('<RED>'.$paddedSpace.'</RED>');
    $this->colorizer->replay();
    // test
    ob_start();
    $this->printer->printLargeBox('My text', 'RED');
    $result = ob_get_clean();
    // assertions
    $this->is($result, "\n<RED>".$paddedSpace."</RED>\n<RED>".$paddedText."</RED>\n<RED>".$paddedSpace."</RED>\n\n", 'The result was colorized and printed');
  }

  public function testPrinterWorksWithoutColorizer()
  {
    // fixtures
    $this->printer = new LimePrinter();
    // test
    ob_start();
    $this->printer->printText('My text');
    $result = ob_get_clean();
    // assertions
    $this->is($result, 'My text', 'The result was printed');
  }

  public function testStringsInUnformattedTextAreAutomaticallyFormatted()
  {
    // fixtures
    $this->colorizer->colorize('"Test string"', LimePrinter::STRING)->returns('<BLUE>"Test string"</BLUE>');
    $this->colorizer->replay();
    // test
    ob_start();
    $this->printer->printText('My text with a "Test string"');
    $result = ob_get_clean();
    // assertions
    $this->is($result, 'My text with a <BLUE>"Test string"</BLUE>', 'The result was colorized and printed');
  }

  public function testIntegersInUnformattedTextAreAutomaticallyFormatted()
  {
    // fixtures
    $this->colorizer->colorize('123', LimePrinter::NUMBER)->returns('<BLUE>123</BLUE>');
    $this->colorizer->replay();
    // test
    ob_start();
    $this->printer->printText('My text with an integer: 123');
    $result = ob_get_clean();
    // assertions
    $this->is($result, 'My text with an integer: <BLUE>123</BLUE>', 'The result was colorized and printed');
  }

  public function testIntegersWithinWordsAreNotFormatted()
  {
    // test
    ob_start();
    $this->printer->printText('My text with an inte123ger');
    $result = ob_get_clean();
    // assertions
    $this->is($result, 'My text with an inte123ger', 'The result was not colorized and printed');
  }

  public function testFloatsInUnformattedTextAreAutomaticallyFormatted()
  {
    // fixtures
    $this->colorizer->colorize('1.23', LimePrinter::NUMBER)->returns('<BLUE>1.23</BLUE>');
    $this->colorizer->replay();
    // test
    ob_start();
    $this->printer->printText('My text with a float: 1.23');
    $result = ob_get_clean();
    // assertions
    $this->is($result, 'My text with a float: <BLUE>1.23</BLUE>', 'The result was colorized and printed');
  }

  public function testBooleansInUnformattedTextAreAutomaticallyFormatted()
  {
    // fixtures
    $this->colorizer->colorize('true', LimePrinter::BOOLEAN)->returns('<BLUE>true</BLUE>');
    $this->colorizer->colorize('false', LimePrinter::BOOLEAN)->returns('<BLUE>false</BLUE>');
    $this->colorizer->replay();
    // test
    ob_start();
    $this->printer->printText('My text with true and false');
    $result = ob_get_clean();
    // assertions
    $this->is($result, 'My text with <BLUE>true</BLUE> and <BLUE>false</BLUE>', 'The result was colorized and printed');
  }
}