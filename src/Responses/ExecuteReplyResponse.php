<?php

namespace JupyterPhpKernel\Responses;

class ExecuteReplyResponse extends Response
{
  public function __construct(int $execution_count, string $status, string $session_id, array $parent_header = [], array $ids = [])
  {
    $content = [
      'execution_count' => $execution_count,
      'status' => $status,
    ];

    parent::__construct(self::EXECUTE_REPLY, $session_id, $content, $parent_header, [], $ids);
  }
}