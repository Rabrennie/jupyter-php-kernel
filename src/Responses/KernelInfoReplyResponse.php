<?php

namespace JupyterPhpKernel\Responses;

class KernelInfoReplyResponse extends Response
{
  public function __construct(string $session_id, array $parent_header = [], array $ids = [])
  {
    $content = [
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
    ];

    parent::__construct(self::KERNEL_INFO_REPLY, $session_id, $content, $parent_header, [], $ids);
  }
}