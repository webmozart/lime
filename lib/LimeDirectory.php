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

/**
 * A directory from which test files can be loaded.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeDirectory implements LimeLoadable
{
  protected
    $path          = null,
    $pattern       = null,
    $executable    = null,
    $labels        = array();

  /**
   * Constructor.
   *
   * @param string $path                The directory path
   * @param string $pattern             The pattern that all loaded files must
   *                                    match
   * @param LimeExecutable $executable  The executable used to run the files
   * @param array $labels               The labels of the files
   */
  public function __construct($path, $pattern, LimeExecutable $executable, array $labels = array())
  {
    if (!is_dir($path))
    {
      throw new InvalidArgumentException(sprintf('The directory "%s" does not exist', $path));
    }

    $this->path = $path;
    $this->pattern = $pattern;
    $this->executable = $executable;
    $this->labels = $labels;
  }

  /**
   * (non-PHPdoc)
   * @see LimeLoadable#loadFiles()
   */
  public function loadFiles()
  {
    $directoryIterator = new RecursiveDirectoryIterator($path);
    $recursiveIterator = new RecursiveIteratorIterator($iterator);
    $filteredIterator = new RegexIterator($iterator, $this->pattern);

    $files = array();

    foreach (iterator_to_array($filteredIterator) as $path)
    {
      $files[] = new LimeFile($path, $this->executable, $this->labels);
    }

    return $files;
  }
}