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

$t = new LimeTest(5);


$t->diag('->autoload() loads class files by class name');

$autoloader = new LimeAutoloader();
$t->is($autoloader->autoload('LimeCoverage'), true, 'Returns true if a class can be loaded');
$t->is($autoloader->autoload('Foo'), false, 'Does not load classes that do not begin with "Lime"');
$t->is($autoloader->autoload('LimeFoo'), false, 'Does not load classes that do not exist');


$t->diag('->autoload() loads old class names if legacy mode is enabled');

$t->is($autoloader->autoload('lime_test'), false, 'Does not load old classes in normal mode');
LimeAutoloader::enableLegacyMode();
$t->is($autoloader->autoload('lime_test'), true, 'Loads old classes in legacy mode');
