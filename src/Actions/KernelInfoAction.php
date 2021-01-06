<?php

namespace JupyterPhpKernel\Actions;

use JupyterPhpKernel\Requests\Request;
use JupyterPhpKernel\Responses\KernelInfoReplyResponse;

class KernelInfoAction extends Action
{
    protected function run(Request $request)
    {
        $response = new KernelInfoReplyResponse($request);
        $this->kernel->sendShellMessage($response);
    }
}
