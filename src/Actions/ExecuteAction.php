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
            $code = $this->cleanCode($request->content['code']);
            $ret = $this->kernel->shell->execute($code);
            $this->kernel->shell->writeReturnValue($ret, true);
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

    private function cleanCode(string $code)
    {
        return preg_replace('/^(<\?(php)?)*(\?>)*/m', '', trim($code));
    }
}
