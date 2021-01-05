<?php

namespace JupyterPhpKernel\Responses;

use JupyterPhpKernel\Requests\Request;

class StatusResponse extends Response
{
    public function __construct(string $status, Request $request)
    {
        $content = ['execution_state' => $status];
        parent::__construct(self::STATUS, $request, $content);
    }
}
