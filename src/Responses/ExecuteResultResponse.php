<?php

namespace JupyterPhpKernel\Responses;

class ExecuteResultResponse extends Response
{
  public function __construct(int $execution_count, string $result, string $session_id, array $parent_header = [], array $ids = [])
  {
    $content = [
      'execution_count' => $execution_count,
      'data' => [
        'text/plain' => $result
      ],
    ];

    parent::__construct(self::EXECUTE_RESULT, $session_id, $content, $parent_header, [], $ids);
  }
}