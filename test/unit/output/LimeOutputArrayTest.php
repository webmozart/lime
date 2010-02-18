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

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(2);

// @Before

  $output = new LimeOutputArray();


// @After

  $output = null;


// @Test: The array is constructed correctly

  // fixtures
  $expected = array(
    array(
      'file' => '/test/file1',
      'tests' => array(
        1 => array(
          'line' => 11,
          'file' => '/test/file1',
          'message' => 'Test message 1',
          'status' => true,
        ),
        2 => array(
          'line' => 22,
          'file' => '/test/file1',
          'message' => 'Test message 2',
          'status' => false,
        ),
      ),
      'stats' => array(
        'plan' => 3,
        'total' => 2,
        'failed' => array(2),
        'passed' => array(1),
        'skipped' => array(),
      ),
    ),
    array(
      'file' => '/test/file2',
      'tests' => array(
        1 => array(
          'line' => 33,
          'file' => '/test/file2',
          'message' => 'Test message 3',
          'status' => false,
          'error' => 'error message',
        ),
        2 => array(
          'line' => 44,
          'file' => '/test/file2',
          'message' => 'Test message 4',
          'status' => true,
        ),
        3 => array(
          'line' => 55,
          'file' => '/test/file2',
          'message' => 'Test message 5',
          'status' => true,
        ),
      ),
      'stats' => array(
        'plan' => 2,
        'total' => 3,
        'failed' => array(1),
        'passed' => array(2),
        'skipped' => array(3),
      ),
    ),
  );
  // test
  $output->focus('/test/file1');
  $output->plan(3);
  $output->pass('Test message 1', '/test/file1', 11);
  $output->fail('Test message 2', '/test/file1', 22);
  $output->focus('/test/file2');
  $output->plan(2);
  $output->fail('Test message 3', '/test/file2', 33, 'error message');
  $output->pass('Test message 4', '/test/file2', 44);
  $output->skip('Test message 5', '/test/file2', 55);
  // assertions
  $t->is($output->toArray(), $expected, 'The array is correct');


// @Test: The array is constructed correctly when switching focus

  // fixtures
  $expected = array(
    array(
      'file' => '/test/file1',
      'tests' => array(
        1 => array(
          'line' => 11,
          'file' => '/test/file1',
          'message' => 'Test message 1',
          'status' => true,
        ),
        2 => array(
          'line' => 22,
          'file' => '/test/file1',
          'message' => 'Test message 2',
          'status' => false,
        ),
      ),
      'stats' => array(
        'plan' => 3,
        'total' => 2,
        'failed' => array(2),
        'passed' => array(1),
        'skipped' => array(),
      ),
    ),
    array(
      'file' => '/test/file2',
      'tests' => array(
        1 => array(
          'line' => 33,
          'file' => '/test/file2',
          'message' => 'Test message 3',
          'status' => false,
          'error' => 'error message',
        ),
        2 => array(
          'line' => 44,
          'file' => '/test/file2',
          'message' => 'Test message 4',
          'status' => true,
        ),
        3 => array(
          'line' => 55,
          'file' => '/test/file2',
          'message' => 'Test message 5',
          'status' => true,
        ),
      ),
      'stats' => array(
        'plan' => 2,
        'total' => 3,
        'failed' => array(1),
        'passed' => array(2),
        'skipped' => array(3),
      ),
    ),
  );
  // test
  $output->focus('/test/file1');
  $output->plan(3);
  $output->pass('Test message 1', '/test/file1', 11);
  $output->focus('/test/file2');
  $output->plan(2);
  $output->fail('Test message 3', '/test/file2', 33, 'error message');
  $output->pass('Test message 4', '/test/file2', 44);
  $output->focus('/test/file1');
  $output->fail('Test message 2', '/test/file1', 22);
  $output->focus('/test/file2');
  $output->skip('Test message 5', '/test/file2', 55);
  // assertions
  $t->is($output->toArray(), $expected, 'The array is correct');
