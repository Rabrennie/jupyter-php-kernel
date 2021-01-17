<?php

namespace JupyterPhpKernel\Handlers;

class HbMessageHandler extends MessageHandler implements IMessageHandler
{
    public function handle($message)
    {
        $this->kernel->sendHbMessage($message);
    }
}
