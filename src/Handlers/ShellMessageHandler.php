<?php

namespace JupyterPhpKernel\Handlers;

use JupyterPhpKernel\Actions\Action;
use JupyterPhpKernel\Actions\ExecuteAction;
use JupyterPhpKernel\Actions\KernelInfoAction;
use JupyterPhpKernel\Kernel;
use JupyterPhpKernel\Requests\Request;

class ShellMessageHandler
{
    public const KERNEL_INFO_REQUEST = 'kernel_info_request';
    public const EXECUTE_REQUEST = 'execute_request';

    protected const ACTION_MAP = [
        self::KERNEL_INFO_REQUEST => KernelInfoAction::class,
        self::EXECUTE_REQUEST => ExecuteAction::class,
    ];

    private Kernel $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function handle(Request $request)
    {
        $msg_type = $request->header['msg_type'];
        if (!isset(self::ACTION_MAP[$msg_type])) {
            return;
        }

        $action_class = self::ACTION_MAP[$msg_type];
        /** @var Action $action */
        $action = new $action_class($this->kernel);
        $action->execute($request);
    }
}
