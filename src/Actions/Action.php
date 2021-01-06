<?php

namespace JupyterPhpKernel\Actions;

use JupyterPhpKernel\Kernel;
use JupyterPhpKernel\Requests\Request;

abstract class Action
{
    protected Kernel $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function execute(Request $request)
    {
        $this->kernel->sendStatusMessage('busy', $request);
        $this->run($request);
        $this->kernel->sendStatusMessage('idle', $request);
    }

    abstract protected function run(Request $request);
}
