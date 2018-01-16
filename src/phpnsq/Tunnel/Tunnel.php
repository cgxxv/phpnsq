<?php

namespace OkStuff\PhpNsq\Tunnel;

use Exception;
use OkStuff\PhpNsq\Wire\Writer;

class Tunnel
{
    private $sock;
    private $config;
    private $writer = [];
    private $reader = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function read($len = 0)
    {
        $data = '';
        $timeout = $this->config->get("readTimeout")["default"];
        $this->reader = [$sock = $this->getSock()];
        while (strlen($data) < $len) {
            $readable = $this->streamSelect($this->reader, $this->writer, $timeout);
            if ($readable > 0) {
                $buffer = @stream_socket_recvfrom($sock, $len);
                if (empty($buffer)) {
                    throw new Exception("Read 0 bytes from {$this->config->host}:{$this->config->port}");
                }
            } else if ($readable === 0) {
                throw new Exception("Timed out reading {$len} bytes from {$this->config->host}:{$this->config->port} after {$timeout} seconds");
            } else {
                throw new Exception("Could not read {$len} bytes from {$this->config->host}:{$this->config->port}");
            }
            $data .= $buffer;
            $len -= strlen($buffer);
        }
        return $data;
    }

    public function write($buffer)
    {
        $timeout = $this->config->get("writeTimeout")["default"];
        $this->writer = [$sock = $this->getSock()];
        while (strlen($buffer) > 0) {
            $writable = $this->streamSelect($this->reader, $this->writer, $timeout);
            if ($writable > 0) {
                $written = @stream_socket_sendto($sock, $buffer);
                if (0 >= $written) {
                    throw new Exception("Could not write " . strlen($buffer) . " bytes to {$this->config->host}:{$this->config->port}");
                }
                $buffer = substr($buffer, $written);
            } else if ($writable === 0) {
                throw new Exception("Time out writing " . strlen($buffer) . " bytes to {$this->config->host}:{$this->config->port} after {$timeout} seconds");
            } else {
                throw new Exception("Could not write " . strlen($buffer) . " bytes to {$this->config->host}:{$this->config->port}");
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
            $this->sock = pfsockopen($this->config->host, $this->config->port, $errno, $errstr);
            if (false === $this->sock) {
                throw new Exception("Could not connect to {$this->config->host}:{$this->config->port} [{$errno}]:[{$errstr}]");
            };

            if (false === $this->config->get("blocking")) {
                stream_set_blocking($this->sock, 0);
            }

            $this->write(Writer::MAGIC_V2);
        }

        return $this->sock;
    }

    private function streamSelect(array &$read, array &$write, $timeout)
    {
        if ($read || $write) {
            $except = null;

            return @stream_select($read, $write, $except, $timeout === null ? null : 0, $timeout);
        }

        $timeout && usleep($timeout);

        return 0;
    }
}
