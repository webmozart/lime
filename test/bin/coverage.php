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

$suite = new LimeHarness(array('base_dir' => $baseDir));

$suite->registerGlob($baseDir.'/unit/*Test.php');
$suite->registerGlob($baseDir.'/unit/*/*Test.php');

$c = new LimeCoverage($suite, array(
  'extension' => '.php',
  'verbose'   => false,
  'base_dir'  => $baseDir.'/../lib',
));

$c->registerGlob($baseDir.'/../lib/*.php');
$c->registerGlob($baseDir.'/../lib/*/*.php');
$c->registerGlob($baseDir.'/../lib/*/*/*.php');
$c->registerGlob($baseDir.'/../lib/*/*/*/*.php');

$c->run();
