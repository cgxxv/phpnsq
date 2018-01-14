<?php

namespace OkStuff\PhpNsq\Tunnel;

use Exception;
use OkStuff\PhpNsq\Wire\Writer;

class Tunnel
{
    public $socket;

    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->socket = $this->socket();
        $this->magic();
    }

    public function read($len = 0)
    {
        $data = "";
        while ($out = socket_read($this->socket, 2048)) {
            $data .= $out;

            if (strlen($data) >= $len && $len > 0) {
                break;
            }
        }

        return $data;
    }

    public function write($data)
    {
        $result = socket_write($this->socket, $data, strlen($data));
        if (false === $result) {
            throw new Exception("socket_write() failed.\nReason: ($result) " . socket_strerror(socket_last_error($this->socket)));
        }

        return $this;
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }

    private function socket()
    {
        $socket = null;
        if ($this->socket == null) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (false === $socket) {
                throw new Exception("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
            }

            $result = socket_connect($socket, $this->config->host, $this->config->port);
            if (false === $result) {
                throw new Exception("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)));
            }
        }

        return $socket;
    }

    private function magic()
    {
        $this->write(Writer::MAGIC_V2);
    }
}
