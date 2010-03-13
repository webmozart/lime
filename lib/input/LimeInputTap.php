<?php

class LimeInputTap extends LimeInput
{
  public function __construct(LimeOutputInterface $output)
  {
    parent::__construct($output);
  }

  public function parse($data)
  {
    $this->buffer .= $data;

    while (!$this->done())
    {
      if (preg_match('/^(.+)\n/', $this->buffer, $matches))
      {
        $this->buffer = substr($this->buffer, strlen($matches[0]));
        $line = $matches[0];

        if (preg_match('/^1\.\.(\d+)\n/', $line, $matches))
        {
        }
        else if (preg_match('/^(not )?ok \d+( - (.+?))?( # (SKIP|TODO)( .+)?)?\n/', $line, $matches))
        {
          $message = count($matches) > 2 ? $matches[3] : '';

          if (count($matches) > 5)
          {
            if ($matches[5] == 'SKIP')
            {
              $this->output->skip($message, '', 0, '', '', count($matches) > 6 ? trim($matches[6]) : '');
            }
            else
            {
              $this->output->todo($message, '', '', '');
            }
          }
          else if (count($matches) > 1 && $matches[1] == 'not ')
          {
            $this->output->fail($message, '', 0, '', '');
          }
          else
          {
            $this->output->pass($message, '', 0, '', '');
          }
        }
      }
      else
      {
        break;
      }
    }

    $this->clearErrors();
  }

  public function done()
  {
    return empty($this->buffer);
  }
}