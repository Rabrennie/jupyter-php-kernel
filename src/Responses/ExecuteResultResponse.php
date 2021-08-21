<?php

namespace JupyterPhpKernel\Responses;

use JupyterPhpKernel\Requests\Request;

class ExecuteResultResponse extends Response
{
    public function __construct(int $execution_count, string $result, Request $request)
    {
        $content = [
            'execution_count' => $execution_count,
            'data' => [
                'text/plain' => $result
            ],
            'metadata' => (object)[],
        ];

        parent::__construct(self::EXECUTE_RESULT, $request, $content);
    }
}
