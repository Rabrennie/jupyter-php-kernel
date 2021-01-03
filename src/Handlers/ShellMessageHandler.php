<?php

namespace JupyterPhpKernel\Handlers;

use JupyterPhpKernel\Kernel;
use JupyterPhpKernel\Responses\Response;
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

  public function handle(array $message)
  {
    $ids = $this->getIds($message);
    [$hmac, $header, $parent_header, $metadata, $content] = $this->getData($message);

    $header = json_decode($header, true);
    $content = json_decode($content, true);

    if ($header['msg_type'] === self::KERNEL_INFO_REQUEST) {
      $this->kernel->sendStatusMessage('busy', $header);
      $response = new Response(
        Response::KERNEL_INFO_REPLY,
        $this->kernel->session_id,
        [
          'protocol_version' => '5.3',
          'implementation' => 'jupyter-php',
          'implementation_version' => '0.1.0',
          'banner' => 'Jupyter-PHP Kernel',
          'language_info' => [
            'name' => 'PHP',
            'version' => \phpversion(),
            'mimetype' => 'text/x-php',
            'file_extension' => '.php',
            'pygments_lexer' => 'PHP',
          ],
          'status' => 'ok',
        ],
        $header,
        [],
        $ids
      );
      $this->kernel->sendShellMessage($response);
      $this->kernel->sendStatusMessage('idle', $header);
    }

    if ($header['msg_type'] === self::EXECUTE_REQUEST) {
      $this->kernel->sendStatusMessage('busy', $header);
      $output = $this->getOutput();
      $stream = $output->getStream();
      $this->kernel->shell->setOutput($output);
      $this->kernel->shell->execute($content['code']);
      \rewind($stream);
      $streamContents = \stream_get_contents($stream);
      // echo ($streamContents);
      $response = new Response(
        'execute_reply',
        $this->kernel->session_id,
        [
          'execution_count' => ++$this->kernel->execution_count,
          'status' => 'ok',
        ],
        $header,
        [],
        $ids
      );
      $this->kernel->sendExecuteResultMessage(['execution_count' => $this->kernel->execution_count, 'data' => ['text/plain' => $streamContents]], $header);
      $this->kernel->sendShellMessage($response);
      $this->kernel->sendStatusMessage('idle', $header);
    }
  }

  protected function getIds(array $message)
  {
    return array_splice($message, 0, $this->getDelimeterIndex($message));
  }

  protected function getData(array $message)
  {
    return array_splice($message, 2);
  }

  protected function getDelimeterIndex(array $message)
  {
    return array_search('<IDS|MSG>', $message);
  }

  private function getOutput()
  {
    $stream = \fopen('php://memory', 'w+');
    $this->streams[] = $stream;

    $output = new StreamOutput($stream, StreamOutput::VERBOSITY_NORMAL, false);

    return $output;
  }
}
