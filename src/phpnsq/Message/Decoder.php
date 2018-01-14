<?php

namespace OkStuff\PhpNsq\Message;

use Exception;
use OkStuff\PhpNsq\Tunnel\Tunnel;
use OkStuff\PhpNsq\Utility\IntPacker;
use RuntimeException;

class Decoder
{
    const TYPE_RESPONSE = 0;
    const TYPE_ERROR = 1;
    const TYPE_MESSAGE = 2;

    private $tunnel;
    private $message;

    public function bindMessage(Message $message)
    {
        if (null === $this->message) {
            $this->message = $message;
        }

        return $this;
    }

    public function bindTunnel(Tunnel $tunnel)
    {
        if (null === $this->tunnel) {
            $this->tunnel = $tunnel;
        }

        return $this;
    }

    // DecodeMessage deserializes data (as []byte) and creates a new Message
    // message format:
    //  [x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]...
    //  |       (int64)        ||    ||      (hex string encoded in ASCII)           || (binary)
    //  |       8-byte         ||    ||                 16-byte                      || N-byte
    //  ------------------------------------------------------------------------------------------...
    //    nanosecond timestamp    ^^                   message ID                       message body
    //                         (uint16)
    //                          2-byte
    //                         attempts

    public function getMessage()
    {
        $message = $this->message;

        if (true === $message->isDecoded()) {
            return $message;
        }

        $size = $type = 0;
        try {
            $size = $this->readInt();
            $type = $this->readInt();

            $message->setSize($size)->setType($type);
        } catch (Exception $e) {
            throw new Exception("Error reading message frame [$size, $type] (" . $e->getMessage() . ")", NULL, $e);
        }

        try {
            if (self::TYPE_RESPONSE == $type) {
                $message->setResponse($this->readString($size - 4));
            } elseif (self::TYPE_ERROR == $type) {
                $message->setError($this->readString($size - 4));
            } elseif (self::TYPE_MESSAGE == $type) {
                $message->setTimestamp($this->decodeTimestamp())
                    ->setAttempts($this->decodeAttempts())
                    ->setId($this->decodeId())
                    ->setBody($this->decodeBody($size - 30));
            } else {
                throw new Exception($this->readString($size - 4));
            }
        } catch (Exception $e) {
            throw new RuntimeException("Error reading frame details [$size, $type]", NULL, $e);
        }

        $message->setDecoded();

        return $message;
    }

    private function decodeTimestamp()
    {
        return IntPacker::int64($this->tunnel->read(8));
    }

    private function decodeAttempts()
    {
        return IntPacker::uInt16($this->tunnel->read(2));
    }

    private function decodeId()
    {
        return IntPacker::int16($this->tunnel->read(16));
    }

    private function decodeBody($size)
    {
        return $this->readString($size);
    }

    private function readInt()
    {
        list(, $tmp) = unpack("N", $this->tunnel->read(4));

        return sprintf("%u", $tmp);
    }

    private function readString($size)
    {
        $bytes = unpack("c{$size}chars", $this->tunnel->read($size));

        return implode(array_map("chr", $bytes));
    }
}
