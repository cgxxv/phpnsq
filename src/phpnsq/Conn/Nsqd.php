<?php

namespace OkStuff\PhpNsq\Conn;

use OkStuff\PhpNsq\Utils\Logging;
use OkStuff\PhpNsq\Stream\Reader;
use OkStuff\PhpNsq\Stream\Socket;
use OkStuff\PhpNsq\Stream\Writer;

class Nsqd
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
            $readable = Socket::select($this->reader, $this->writer, $timeout);
            if ($readable > 0) {
                $buffer = Socket::recvFrom($sock, $len);
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
            $writable = Socket::select($this->reader, $this->writer, $timeout);
            if ($writable > 0) {
                $buffer = substr($buffer, Socket::sendTo($sock, $buffer));
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
            $this->sock = Socket::client($this->config->host, $this->config->port);

            if (false === $this->config->get("blocking")) {
                stream_set_blocking($this->sock, 0);
            }

            $this->write(Writer::MAGIC_V2);
            $this->auth();

            //FIXME: Really shit php code.
            $tlsConfig=$this->config->get("tlsConfig");
            $context = $this->sock;
            if (null !== $tlsConfig) {
                $this->write(Writer::identify(["tls_v1" => true]));

                if ($tlsConfig["local_cert"]) {
                    if (!file_exists($tlsConfig["local_cert"])) {
                        throw new Exception("Local cert file not exists");
                    }
                    if (!stream_context_set_option($context, 'tcp', 'local_cert', $tlsConfig["local_cert"])) {
                        throw new Exception("Could not set cert");
                    }
                }
                if ($tlsConfig["local_pk"]) {
                    if (!file_exists($tlsConfig["local_pk"])) {
                        throw new Exception("Local pk file not exists");
                    }
                    if (!stream_context_set_option($context, 'tcp', 'local_pk', $tlsConfig["local_pk"])) {
                        throw new Exception("Could not set local_pk");
                    }
                }
                if ($tlsConfig["passphrase"] && !stream_context_set_option($context, 'tcp', 'passphrase', $tlsConfig["passphrase"])) {
                    throw New Exception("Could not set passphrase for your ssl cert");
                }
                if ($tlsConfig["cn_match"] && !stream_context_set_option($context, 'tcp', 'CN_match', $tlsConfig["cn_match"])) {
                    throw new Exception("Could not set CN_match");
                }
                if ($tlsConfig["peer_fingerprint"] && !stream_context_set_option($context, 'tcp', 'peer_fingerprint', $tlsConfig["peer_fingerprint"])) {
                    throw new Exception("Could not set peer_fingerprint");
                }
                stream_context_set_option($context, 'tcp', 'allow_self_signed', true);
                stream_context_set_option($context, 'tcp', 'verify_peer', true);
                stream_context_set_option($context, 'tcp', 'cafile', $tlsConfig["cafile"]);
            }

            $this->sock = $context;
        }

        return $this->sock;
    }

    private function auth()
    {
        if ($this->config->get("authSwitch")) {
            $this->write(Writer::auth($this->config->get("authSecret")));
            $msg = (new Reader())->bindConn($this)->bindFrame()->getMessage();
            (new Logging("PHPNSQ", $this->config->get("logdir")))->info($msg);
        }
    }
}
