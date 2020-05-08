<?php

namespace OkStuff\PhpNsq\Stream;

use Exception;
use OkStuff\PhpNsq\Conn\Nsqd;

class Reader
{
    const TYPE_RESPONSE = 0;
    const TYPE_ERROR    = 1;
    const TYPE_MESSAGE  = 2;

    const HEARTBEAT = "_heartbeat_";
    const OK        = "OK";

    private $conn;
    private $frame;

    public function __construct(Nsqd $conn = null)
    {
        $this->conn = $conn;
    }

    public function bindConn(Nsqd $conn)
    {
        $this->conn = $conn;

        return $this;
    }

    public function bindFrame()
    {
        $size = 0;
        $type = 0;
        try {
            $size = $this->readInt(4);
            $type = $this->readInt(4);
        } catch (Exception $e) {
            throw new Exception("Error reading message frame [$size, $type] ({$e->getMessage()})");
        }

        $frame = [
            "size" => $size,
            "type" => $type,
        ];

        try {
            if (self::TYPE_RESPONSE == $type) {
                $frame["response"] = $this->readString($size - 4);
            } elseif (self::TYPE_ERROR == $type) {
                $frame["error"] = $this->readString($size - 4);
            }
        } catch (Exception $e) {
            throw new Exception("Error reading frame details [$size, $type] ({$e->getMessage()})");
        }

        $this->frame = $frame;

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
        $msg = null;
        if (null !== $this->frame) {
            switch ($this->frame["type"]) {
                case self::TYPE_MESSAGE:
                    $msg = (new Message())->setTimestamp($this->readInt64(8))
                        ->setAttempts($this->readUInt16(2))
                        ->setId($this->readString(16))
                        ->setBody($this->readString($this->frame["size"] - 30))
                        ->setDecoded();
                    break;
                case self::TYPE_RESPONSE:
                    $msg = $this->frame["response"];
                    break;
                case self::TYPE_ERROR:
                    $msg = $this->frame["error"];
                    break;
            }
            
        }

        return $msg;
    }

    public function isMessage()
    {
        return self::TYPE_MESSAGE == $this->frame["type"];
    }

    public function isHeartbeat()
    {
        return $this->isResponse(self::HEARTBEAT);
    }

    public function isOk()
    {
        return $this->isResponse(self::OK);
    }

    public function isResponse($response = null)
    {
        return isset($this->frame["response"])
            && self::TYPE_RESPONSE == $this->frame["type"]
            && (null === $response || $response === $this->frame["response"]);
    }

    private function readInt($size)
    {
        list(, $tmp) = unpack("N", $this->conn->read($size));

        return sprintf("%u", $tmp);
    }

    private function readInt64($size)
    {
        return IntPacker::int64($this->conn->read($size));
    }

    private function readUInt16($size)
    {
        return IntPacker::uInt16($this->conn->read($size));
    }

    private function readString($size)
    {
        $bytes = unpack("c{$size}chars", $this->conn->read($size));

        return implode(array_map("chr", $bytes));
    }
}
