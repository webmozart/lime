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

class LimeOutputXml extends LimeOutput
{
  protected
    $configuration     = null;

  public function __construct(LimeConfiguration $configuration)
  {
    parent::__construct();

    $this->configuration = $configuration;
  }

  public function supportsThreading()
  {
    return true;
  }

  public function comment($message)
  {
  }

  public function flush()
  {
    print $this->toXml();
  }

  public function toXml()
  {
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;

    $dom->appendChild($testsuites = $dom->createElement('testsuites'));
    $testsuites->setAttribute('failures', $this->failed);
    $testsuites->setAttribute('errors', $this->errors);
    $testsuites->setAttribute('tests', $this->total);
    $testsuites->setAttribute('assertions', $this->total);
    $testsuites->setAttribute('skipped', $this->skipped);
    $testsuites->setAttribute('time', round($this->time, 6));

    foreach ($this->files as $file => $result)
    {
      $testsuites->appendChild($testSuite = $dom->createElement('testsuite'));
      $testSuite->setAttribute('name', basename($file, $this->configuration->getSuffix()));
      $testSuite->setAttribute('file', $file);
      $testSuite->setAttribute('failures', $result->failed);
      $testSuite->setAttribute('errors', $result->errors);
      $testSuite->setAttribute('skipped', $result->skipped);
      $testSuite->setAttribute('tests', $result->total);
      $testSuite->setAttribute('assertions', $result->total);
      $testSuite->setAttribute('time', round($result->time, 6));

      foreach ($result->tests as $test)
      {
        $testSuite->appendChild($testCase = $dom->createElement('testcase'));
        $testCase->setAttribute('name', $test['message']);
        $testCase->setAttribute('file', $test['file']);
        $testCase->setAttribute('line', $test['line']);
        $testCase->setAttribute('time', round($test['time'], 6));
        $testCase->setAttribute('assertions', 1);
        if ($test['class'])
        {
          $testCase->setAttribute('classname', $test['class']);
        }
        if ($test['status'] == 'error')
        {
          $testCase->appendChild($failure = $dom->createElement('failure'));
          if (array_key_exists('error', $test))
          {
            $failure->setAttribute('type', $test['error']->getType());
            $failure->setAttribute('file', $test['error']->getFile());
            $failure->setAttribute('line', $test['error']->getLine());
            $failure->appendChild($dom->createTextNode($test['error']->getMessage()));
          }
          else
          {
            $failure->setAttribute('type', 'lime');
          }
        }
      }
    }

    return $dom->saveXml();
  }
}