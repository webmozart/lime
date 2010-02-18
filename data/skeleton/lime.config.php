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

/*
 * Sets the directory where the registered files are searched for.
 */
$config->setBaseDir(dirname(__FILE__));

/**
 * Register your test files here. The file paths can be absolute or relative to
 * the configured base directory.
 *
 * Each test file or bunch of test files needs an executable used to
 * execute the test files. Read the documentation of LimeExecutable for more
 * information. The default executable $lime is provided in this script.
 *
 * Examples:
 *
 * Registers the file MyClassTest.php:
 *
 *   $config->registerFile('test/MyClassTest.php', $lime);
 *
 * Registers all files in a specific directory with the configured suffix
 * (see below):
 *
 *   $config->registerDir('path/to/dir', $lime);
 *
 * Registers all files matched by the given glob:
 *
 *   $config->registerGlob('test/*.test.php', $lime);
 *
 * Registers all file paths returned by the given callback:
 *
 *   function read_test_files()
 *   {
 *     ...
 *   }
 *
 *   $config->registerCallback('read_test_files', $lime);
 *
 * All register*() methods accept an optional parameter which either accepts
 * a single label or an array with multiple labels tjat will be added to all
 * matched files. If a file is matched twice by separate register*() calls,
 * it will have the labels of both calls.
 *
 * Examples:
 *
 *   $config->registerDir('path/to/dir', $lime, 'label');
 *   $config->registerDir('path/to/dir', $lime, array('unit', 'slow'));
 */

$lime = LimeExecutable::php('lime', 'raw', array('--output' => 'raw'));

//$config->registerDir('test', $lime);

/*
 * If you set verbose to true, some test outputs will output additional
 * information. Only supported by some outputs.
 */
$config->setVerbose(false);

/*
 * Enforces colorization in the console output. Only supported by some
 * outputs.
 */
$config->setForceColors(false);

/*
 * Enforces serialization of the output. Only supported by some outputs.
 */
$config->setSerialize(false);

/*
 * Sets the suffix of test files. When directories are scanned for test
 * files, only files with this suffix will be registered.
 *
 * Single tests can be launched by providing the filename without the suffix.
 */
$config->setSuffix('Test.php');

/*
 * Sets the number of processes used for processing test suites. If set
 * to 1, no multiprocessing is used.
 */
$config->setProcesses(1);

/*
 * Sets the output factory used for creating output instances.
 */
$config->setOutputFactory(new LimeOutputFactory($config));

/*
 * Sets the output name for test harnesses. The output name must be known to
 * the configured output factory.
 *
 * With the default factory, this can be one of "suite", "tap", "xml", "array"
 * and "raw".
 */
$config->setSuiteOutput('suite');

/*
 * Sets the output name for single tests. The output name must be known to
 * the configured output factory.
 *
 * With the default factory, this can be one of "suite", "tap", "xml", "array"
 * and "raw".
 */
$config->setTestOutput('tap');
