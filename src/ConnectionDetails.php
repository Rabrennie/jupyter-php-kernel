<?php

namespace JupyterPhpKernel;

class ConnectionDetails
{
    private string $connection_file_path;
    private string $connection_file;
    private array $connection_details;

    public string $ip;
    public string $shell_address;
    public string $iopub_address;
    public string $stdin_address;
    public string $control_address;
    public string $hb_address;

    public string $signature_scheme;
    public string $key;

    public function __construct(string $connection_file_path)
    {
        $this->connection_file_path = $connection_file_path;
    }

    public function read()
    {
        $this->connection_file = file_get_contents($this->connection_file_path);
        $this->connection_details = json_decode($this->connection_file, true);

        $this->ip = $this->connection_details['ip'];

        $this->shell_address = $this->getAddress($this->ip, $this->connection_details['shell_port']);
        $this->iopub_address = $this->getAddress($this->ip, $this->connection_details['iopub_port']);
        $this->stdin_address = $this->getAddress($this->ip, $this->connection_details['stdin_port']);
        $this->control_address = $this->getAddress($this->ip, $this->connection_details['control_port']);
        $this->hb_address = $this->getAddress($this->ip, $this->connection_details['hb_port']);

        $this->signature_scheme = $this->connection_details['signature_scheme'];
        $this->key = $this->connection_details['key'];

        return $this;
    }

    private function getAddress(string $ip, string $port)
    {
        return "$ip:$port";
    }
}
