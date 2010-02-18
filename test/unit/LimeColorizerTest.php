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

require_once dirname(__FILE__).'/../bootstrap/unit.php';

$t = new LimeTest(10);


$t->diag('Text can be colorized with font and color styles');

$c = new LimeColorizer();
$t->is($c->colorize('Hello World', array('bold' => true)),       "\033[1mHello World\033[0m", 'Text can be bold');
$t->is($c->colorize('Hello World', array('underscore' => true)), "\033[4mHello World\033[0m", 'Text can be underscored');
$t->is($c->colorize('Hello World', array('blink' => true)),      "\033[5mHello World\033[0m", 'Text can be blinking');
$t->is($c->colorize('Hello World', array('reverse' => true)),    "\033[7mHello World\033[0m", 'Text can be reversed');
$t->is($c->colorize('Hello World', array('conceal' => true)),    "\033[8mHello World\033[0m", 'Text can be invisible');
$t->is($c->colorize('Hello World', array('fg' => 'white')),      "\033[37mHello World\033[0m", 'Text can have a custom text color');
$t->is($c->colorize('Hello World', array('bg' => 'white')),      "\033[47mHello World\033[0m", 'Text can have a custom background color');
$t->is($c->colorize('Hello World', array('bold' => true, 'fg' => 'black', 'bg' => 'white')), "\033[30;47;1mHello World\033[0m", 'Styles can be combined');


$t->diag('Text styles can be preset using ->setStyle()');

$c = new LimeColorizer();
$c->setStyle('test_style', array('bold' => true, 'fg' => 'black', 'bg' => 'white'));
$t->is($c->colorize('Hello World', 'test_style'), "\033[30;47;1mHello World\033[0m", 'Predefined styles can be used');


$t->diag('Text styles can be preset using backwards compatible ::style()');

LimeAutoloader::enableLegacyMode();
lime_colorizer::style('test_style', array('bold' => true, 'fg' => 'black', 'bg' => 'white'));

$c = new lime_colorizer();
$t->is($c->colorize('Hello World', 'test_style'), "\033[30;47;1mHello World\033[0m", 'Predefined styles can be used');
