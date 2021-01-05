<?php

namespace JupyterPhpKernel\Responses;

use JupyterPhpKernel\Requests\Request;

class ExecuteReplyResponse extends Response
{
    public function __construct(int $execution_count, string $status, Request $request)
    {
        $content = [
            'execution_count' => $execution_count,
            'status' => $status,
        ];

        parent::__construct(self::EXECUTE_REPLY, $request, $content);
    }
}
