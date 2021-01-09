<?php

namespace JupyterPhpKernel\Actions;

use Exception;
use JupyterPhpKernel\Requests\Request;
use JupyterPhpKernel\Responses\ExecuteReplyResponse;
use JupyterPhpKernel\Responses\ExecuteResultResponse;
use Symfony\Component\Console\Output\StreamOutput;

class ExecuteAction extends Action
{
    protected function run(Request $request)
    {
        $output = $this->getOutput();
        $stream = $output->getStream();
        $this->kernel->shell->setOutput($output);
        try {
            $ret = $this->kernel->shell->execute($request->content['code']);
            $this->kernel->shell->writeReturnValue($ret, false);
            rewind($stream);
            $output = stream_get_contents($stream);
        } catch (Exception $e) {
            $output = $e->getMessage();
        }
        $this->kernel->execution_count += 1;
        $this->kernel->sendIOPubMessage(
            new ExecuteResultResponse($this->kernel->execution_count, $output, $request)
        );
        $this->kernel->sendShellMessage(new ExecuteReplyResponse($this->kernel->execution_count, 'ok', $request));
    }

    private function getOutput()
    {
        $stream = fopen('php://memory', 'w+');
        return new StreamOutput($stream, StreamOutput::VERBOSITY_NORMAL, false);
    }
}
