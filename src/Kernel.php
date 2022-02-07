<?php

namespace JupyterPhpKernel;

use JupyterPhpKernel\Handlers\HbMessageHandler;
use JupyterPhpKernel\Handlers\IMessageHandler;
use JupyterPhpKernel\Handlers\IRequestHandler;
use JupyterPhpKernel\Handlers\ShellMessageHandler;
use JupyterPhpKernel\Requests\Request;
use JupyterPhpKernel\Responses\Response;
use JupyterPhpKernel\Responses\StatusResponse;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\ZMQ\Context;
use Ramsey\Uuid\Uuid;
use React\ZMQ\SocketWrapper;
use Psy\Shell;
use Psy\Configuration;
use ZMQ;

class Kernel
{
    private LoopInterface $loop;
    private Context $context;
    public ConnectionDetails $connection_details;
    public string $session_id;

    private SocketWrapper $iopub_socket;
    private SocketWrapper $shell_socket;
    private SocketWrapper $hb_socket;

    public int $execution_count = 0;
    public Shell $shell;

    public function __construct(ConnectionDetails $connection_details)
    {
        $this->connection_details = $connection_details;
        $this->session_id = Uuid::uuid4()->toString();
        $this->shell = new Shell($this->getConfig());
    }

    public function run()
    {
        $this->loop = Factory::create();
        $this->context = new Context($this->loop);

        $this->shell_socket = $this->createSocket(
            ZMQ::SOCKET_ROUTER,
            $this->connection_details->shell_address,
            new ShellMessageHandler($this)
        );
        $this->iopub_socket = $this->createSocket(ZMQ::SOCKET_PUB, $this->connection_details->iopub_address);
        $this->hb_socket = $this->createSocket(
            ZMQ::SOCKET_REP,
            $this->connection_details->hb_address,
            new HbMessageHandler($this)
        );
        $stdin_socket = $this->createSocket(ZMQ::SOCKET_ROUTER, $this->connection_details->stdin_address);
        $control_socket = $this->createSocket(ZMQ::SOCKET_ROUTER, $this->connection_details->control_address);

        $this->loop->run();
    }

    protected function createSocket($type, $address, $handler = null)
    {
        $socket = $this->context->getSocket($type);
        $socket->bind("tcp://$address");

        $socket->on('error', function ($e) {
            echo ($e->getMessage());
        });

        $socket->on('messages', function ($message) use ($handler) {
            if ($handler === null) {
                return;
            }

            if ($handler instanceof IMessageHandler) {
                $handler->handle($message);
            } elseif ($handler instanceof IRequestHandler) {
                $handler->handle(new Request($message, $this->session_id));
            }
        });

        return $socket;
    }

    public function sendStatusMessage(string $status, Request $request)
    {
        $this->sendIOPubMessage(new StatusResponse($status, $request));
    }

    public function sendIOPubMessage(Response $response)
    {
        $message = $response->toMessage($this->connection_details->key, $this->connection_details->signature_scheme);
        $this->iopub_socket->send($message);
    }

    public function sendShellMessage(Response $response)
    {
        $message = $response->toMessage($this->connection_details->key, $this->connection_details->signature_scheme);
        $this->shell_socket->send($message);
    }

    public function sendHbMessage($message)
    {
        $this->hb_socket->send($message);
    }

    private function getConfig(array $config = [])
    {
        $dir = tempnam(\sys_get_temp_dir(), 'jupyter_php_kernel');
        unlink($dir);

        $defaults = [
            'configDir'  => $dir,
            'dataDir'    => $dir,
            'runtimeDir' => $dir,
            'colorMode'  => Configuration::COLOR_MODE_FORCED,
            'rawOutput'  => true,
        ];

        return new Configuration(\array_merge($defaults, $config));
    }
}
