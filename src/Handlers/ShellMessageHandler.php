<?php

namespace JupyterPhpKernel\Handlers;

use JupyterPhpKernel\Kernel;
use JupyterPhpKernel\Responses\Response;

class ShellMessageHandler
{
  const KERNEL_INFO_REQUEST = 'kernel_info_request';

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
}
