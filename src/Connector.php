<?php

namespace OkStuff\PHPNSQ;

class Connector
{
    private $messagesInFlight = 0;
	private $maxRdyCount = 0;
	private $rdyCount = 0;
	private $lastRdyCount = 0;
	private $lastRdyTimestamp = 0;
	private $lastMsgTimestamp = 0;

    private $config;

	// conn    *net.TCPConn
	// tlsConn *tls.Conn
    public $addr;
    public $port;

    private $connDelegate;

	private $logger;
	private $logLvl;
	private $logFmt;

    private $readLoopRunning;
    
    private $socket;

    public function __construct($addr, $port, $config, $connDelegate)
    {
        $this->addr = $addr;
        $this->port = $port;
        $this->config = $config;
        $this->connDelegate = $connDelegate;

        if ($this->socket == null) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket === false) {
                throw new Exception("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
            }
            $this->socket = $socket;
        }

        $result = socket_connect($this->socket, $this->addr, $this->port);
        if ($result === false) {
            throw new Exception("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($this->socket)));
        } else {
            echo "OK.\n";
        }
    }

    public function read()
    {
        $data = "";
        while ($out = socket_read($this->socket, 2048)) {
            $data .= $out;
        }

        return $data;
    }

    public function write($data)
    {
        $result = socket_write($this->socket, $data, strlen($data));
        if (false === $result) {
            throw new Exception("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($this->socket)));
        }

        return $result;
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }
}