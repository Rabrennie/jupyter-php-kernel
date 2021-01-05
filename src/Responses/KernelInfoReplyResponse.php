<?php

namespace JupyterPhpKernel\Responses;

use JupyterPhpKernel\Requests\Request;

class KernelInfoReplyResponse extends Response
{
    public function __construct(Request $request)
    {
        $content = [
            'protocol_version' => '5.3',
            'implementation' => 'jupyter-php',
            'implementation_version' => '0.1.0',
            'banner' => 'Jupyter-PHP Kernel',
            'language_info' => [
                'name' => 'PHP',
                'version' => phpversion(),
                'mimetype' => 'text/x-php',
                'file_extension' => '.php',
                'pygments_lexer' => 'PHP',
            ],
            'status' => 'ok',
        ];

        parent::__construct(self::KERNEL_INFO_REPLY, $request, $content);
    }
}
