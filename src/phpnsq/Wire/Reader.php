<?php

namespace OkStuff\PhpNsq\Wire;

use OkStuff\PhpNsq\Message\Decoder;
use OkStuff\PhpNsq\Message\Message;
use OkStuff\PhpNsq\Tunnel\Tunnel;
use RuntimeException;

class Reader
{
    const HEARTBEAT = '_heartbeat_';
    const OK = 'OK';

    private $tunnel;
    private $message;
    private $decoder;

    public function __construct()
    {
        $this->message = new Message();
        $this->decoder = new Decoder();
    }

    public function bindTunnel(Tunnel $tunnel)
    {
        $this->tunnel = $tunnel;

        return $this;
    }

    public function getDecoder()
    {
        if (null === $this->tunnel) {
            throw new RuntimeException("Reader tunnel not exists.");
        }

        return $this->decoder->bindTunnel($this->tunnel);
    }

    public function getMessage()
    {
        return $this->getDecoder()->bindMessage($this->message)->getMessage();
    }

    public function isHeartbeat()
    {
        return $this->isResponse(self::HEARTBEAT);
    }

    public function isResponse($response = null)
    {
        return true === $this->getMessage()->isDecoded()
            && Decoder::TYPE_RESPONSE === $this->getMessage()->getType()
            && (null === $response || $response === $this->getMessage()->getResponse());
    }

    public function isMessage()
    {
        return true === $this->getMessage()->isDecoded()
            && Decoder::TYPE_MESSAGE === $this->getMessage()->getType();
    }

    public function isOk()
    {
        return $this->isResponse(self::OK);
    }
}
