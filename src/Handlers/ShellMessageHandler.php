<?php

namespace JupyterPhpKernel\Handlers;

use JupyterPhpKernel\Kernel;
use JupyterPhpKernel\Requests\Request;
use JupyterPhpKernel\Responses\ExecuteReplyResponse;
use JupyterPhpKernel\Responses\ExecuteResultResponse;
use JupyterPhpKernel\Responses\KernelInfoReplyResponse;
use Symfony\Component\Console\Output\StreamOutput;

class ShellMessageHandler
{
    const KERNEL_INFO_REQUEST = 'kernel_info_request';
    const EXECUTE_REQUEST = 'execute_request';

    private Kernel $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function handle(Request $request)
    {
        if ($request->header['msg_type'] === self::KERNEL_INFO_REQUEST) {
            $this->kernel->sendStatusMessage('busy', $request);
            $response = new KernelInfoReplyResponse($request);
            $this->kernel->sendShellMessage($response);
            $this->kernel->sendStatusMessage('idle', $request);
        }

        if ($request->header['msg_type'] === self::EXECUTE_REQUEST) {
            $this->kernel->sendStatusMessage('busy', $request);
            $output = $this->getOutput();
            $stream = $output->getStream();
            $this->kernel->shell->setOutput($output);
            $this->kernel->shell->execute($request->content['code']);
            \rewind($stream);
            $streamContents = \stream_get_contents($stream);
            $response = new ExecuteReplyResponse(++$this->kernel->execution_count, 'ok', $request);
            $this->kernel->sendIOPubMessage(new ExecuteResultResponse($this->kernel->execution_count, $streamContents, $request));
            $this->kernel->sendShellMessage($response);
            $this->kernel->sendStatusMessage('idle', $request);
        }
    }

    private function getOutput()
    {
        $stream = \fopen('php://memory', 'w+');
        $this->streams[] = $stream;

        $output = new StreamOutput($stream, StreamOutput::VERBOSITY_NORMAL, false);

        return $output;
    }
}
