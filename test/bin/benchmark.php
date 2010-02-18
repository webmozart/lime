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

// up to how many parallel processes should I test?
define('PROCESSES_LIMIT', 10);

// how often should I run the test suite for each process count?
define('TEST_RUNS', 4);

// which test suite should I run for reference?
define('TEST_SCRIPT', dirname(__FILE__).'/prove.php');

// roger!

// the resulting number of test runs is PROCESSES_LIMIT*TEST_RUNS

include dirname(__FILE__).'/../../lib/LimeAutoloader.php';

LimeAutoloader::register();

$processCounts = array();
$stats = array();

for ($i = 1; $i <= PROCESSES_LIMIT; ++$i)
{
  $stats[$i] = array();

  for ($j = 1; $j <= TEST_RUNS; ++$j)
  {
    $processCounts[] = $i;
  }
}

shuffle($processCounts);

foreach ($processCounts as $i => $processCount)
{
  $time = microtime(true);

  echo "Running ".($i+1)." of ".count($processCounts)." ($processCount processes)\n";

  $command = new LimeCommand(TEST_SCRIPT, array('processes' => $processCount));
  $command->execute();

  $stats[$processCount][] = microtime(true) - $time;
}

foreach ($stats as $key => $stat)
{
  $stats[$key] = array_sum($stat)/count($stat);
}

echo "\n";
echo "BENCHMARK RESULTS\n";
echo "=================\n";

foreach ($stats as $nb => $stat)
{
  echo str_pad("$nb processes", 20, ' ').str_pad(round($stat, 2).' sec', 10, ' ').round((-100)*($stat-$stats[1])/$stats[1], 2)."%\n";
}