<?php

namespace JupyterPhpKernel\Responses;

class StatusResponse extends Response
{
  public function __construct(string $status, string $session_id, array $parent_header = [])
  {
    $content = ['execution_state' => $status];
    parent::__construct(self::STATUS, $session_id, $content, $parent_header, [], []);
  }
}