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

require_once(dirname(__FILE__).'/../../lib/LimeAutoloader.php');

LimeAutoloader::register();

$baseDir = realpath(dirname(__FILE__).'/..');

$printer = new LimePrinter(new LimeColorizer());
$printer->printLine('Source Lines Of Code', LimePrinter::HAPPY);

$fileLines = 0;
$testLines = 0;
$lexer = new LimeLexerCodeLines();

$suite = new LimeHarness();

$suite->registerGlob($baseDir.'/../lib/*.php');
$suite->registerGlob($baseDir.'/../lib/*/*.php');
$suite->registerGlob($baseDir.'/../lib/*/*/*.php');
$suite->registerGlob($baseDir.'/../lib/*/*/*/*.php');
$suite->registerGlob($baseDir.'/../lib/*/*/*/*/*.php');

foreach ($suite->getFiles() as $file)
{
  $fileLines += count($lexer->parse($file));
}

$printer->printLine(sprintf('Classes:       %d', $fileLines));

$suite = new LimeHarness();

$suite->registerGlob($baseDir.'/unit/*.php');
$suite->registerGlob($baseDir.'/unit/*/*.php');
$suite->registerGlob($baseDir.'/unit/*/*/*.php');
$suite->registerGlob($baseDir.'/unit/*/*/*/*.php');

foreach ($suite->getFiles() as $file)
{
  $testLines += count($lexer->parse($file));
}

$printer->printLine(sprintf('Tests:         %d', $testLines));
$printer->printLine(sprintf('TOTAL:         %d', $fileLines+$testLines));
$printer->printLine(sprintf('Tests/Classes: %d%%', 100*$testLines / $fileLines));

