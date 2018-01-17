<?php

namespace OkStuff\PhpNsq\Tunnel;

use Exception;
use OkStuff\PhpNsq\Utility\Stream;
use OkStuff\PhpNsq\Wire\Writer;

class Tunnel
{
    private $config;
    private $sock;
    private $writer = [];
    private $reader = [];

    private $identify = false;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function read($len = 0)
    {
        $data         = '';
        $timeout      = $this->config->get("readTimeout")["default"];
        $this->reader = [$sock = $this->getSock()];
        while (strlen($data) < $len) {
            $readable = Stream::select($this->reader, $this->writer, $timeout);
            if ($readable > 0) {
                $buffer = Stream::recvFrom($sock, $len);
                $data   .= $buffer;
                $len    -= strlen($buffer);
            }
        }

        return $data;
    }

    public function write($buffer)
    {
        $timeout      = $this->config->get("writeTimeout")["default"];
        $this->writer = [$sock = $this->getSock()];
        while (strlen($buffer) > 0) {
            $writable = Stream::select($this->reader, $this->writer, $timeout);
            if ($writable > 0) {
                $buffer = substr($buffer, Stream::sendTo($sock, $buffer));
            }
        }

        return $this;
    }

    public function __destruct()
    {
        fclose($this->getSock());
    }

    public function getSock()
    {
        if (null === $this->sock) {
            $this->sock = Stream::pfopen($this->config->host, $this->config->port);

            if (false === $this->config->get("blocking")) {
                stream_set_blocking($this->sock, 0);
            }

            $this->write(Writer::MAGIC_V2);
        }

        return $this->sock;
    }

    //TODO:
    public function setIdentify()
    {
        if (false === $this->identify) {
            $this->write(Writer::identify());
        }

        return $this;
    }
}
