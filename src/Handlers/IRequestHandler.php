<?php

namespace JupyterPhpKernel\Handlers;

use JupyterPhpKernel\Requests\Request;

interface IRequestHandler
{
    public function handle(Request $request);
}
