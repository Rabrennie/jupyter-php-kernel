<?php

namespace JupyterPhpKernel;

use JupyterPhpKernel\Handlers\ShellMessageHandler;
use JupyterPhpKernel\Responses\Response;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\ZMQ\Context;
use Ramsey\Uuid\Uuid;
use React\ZMQ\SocketWrapper;
use Psy\Shell;
use Psy\Configuration;

class Kernel
{
  private LoopInterface $loop;
  private Context $context;
  public ConnectionDetails $connection_details;
  public string $session_id;

  private SocketWrapper $iopub_socket;
  private SocketWrapper $shell_socket;

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

    $this->shell_socket = $this->createSocket(\ZMQ::SOCKET_ROUTER, $this->connection_details->shell_address, new ShellMessageHandler($this));
    $this->iopub_socket = $this->createSocket(\ZMQ::SOCKET_PUB, $this->connection_details->iopub_address);
    $stdin_socket = $this->createSocket(\ZMQ::SOCKET_ROUTER, $this->connection_details->stdin_address);
    $control_socket = $this->createSocket(\ZMQ::SOCKET_ROUTER, $this->connection_details->control_address);
    $hb_socket = $this->createSocket(\ZMQ::SOCKET_REP, $this->connection_details->hb_address);

    $this->loop->run();
  }

  protected function createSocket($type, $address, $handler = null)
  {
    $socket = $this->context->getSocket($type);
    $socket->bind("tcp://$address");

    $socket->on('error', function ($e) {
      echo ($e->getMessage());
    });

    $socket->on('messages', function ($msg) use ($handler, $socket) {
      if ($handler === null) {
        return;
      }

      $handler->handle($msg, $this);
    });

    return $socket;
  }

  public function sendStatusMessage(string $status, array $header)
  {
    $response = new Response('status', $this->session_id, ['execution_state' => $status], $header);
    $this->iopub_socket->send($response->toMessage($this->connection_details->key, $this->connection_details->signature_scheme));
  }

  public function sendExecuteResultMessage(array $content, array $header)
  {

    $response = new Response('execute_result', $this->session_id, $content, $header);
    $this->iopub_socket->send($response->toMessage($this->connection_details->key, $this->connection_details->signature_scheme));
  }

  public function sendShellMessage(Response $response)
  {
    $message = $response->toMessage($this->connection_details->key, $this->connection_details->signature_scheme);
    $this->shell_socket->send($message);
  }

  private function getConfig(array $config = [])
  {
    // Mebbe there's a better way than this?
    $dir = \tempnam(\sys_get_temp_dir(), 'psysh_shell_test_');
    \unlink($dir);

    $defaults = [
      'configDir'  => $dir,
      'dataDir'    => $dir,
      'runtimeDir' => $dir,
      'colorMode'  => Configuration::COLOR_MODE_FORCED,
    ];

    return new Configuration(\array_merge($defaults, $config));
  }
}
