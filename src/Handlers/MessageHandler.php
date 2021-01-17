<?php

namespace JupyterPhpKernel\Handlers;

use JupyterPhpKernel\Kernel;

abstract class MessageHandler
{
    protected Kernel $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
}
