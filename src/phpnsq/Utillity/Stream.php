<?php

namespace OkStuff\PhpNsq\Utility;

use Exception;

class Stream
{
    public static function pfopen($host, $port)
    {
        $socket = pfsockopen($host, $port, $errno, $errstr);
        if (false === $socket) {
            throw new Exception("Could not connect to {$host}:{$port} [{$errno}]:[{$errstr}]");
        }

        return $socket;
    }

    public static function sendTo($socket, $buffer)
    {
        $written = @stream_socket_sendto($socket, $buffer);
        if (0 >= $written) {
            throw new Exception("Could not write " . strlen($buffer) . " bytes to {$socket}");
        }

        return $written;
    }

    public static function recvFrom($socket, $length)
    {
        $buffer = @stream_socket_recvfrom($socket, $length);
        if (empty($buffer)) {
            throw new Exception("Read 0 bytes from {$socket}");
        }

        return $buffer;
    }

    public static function select(array &$read, array &$write, $timeout)
    {
        $streamPool = [
            "read" => $read,
            "write" => $write
        ];
        if ($read || $write) {
            $except = null;

            $available = @stream_select($read, $write, $except, $timeout === null ? null : 0, $timeout);
            if ($available > 0) {
                return $available;
            } else if ($available === 0) {
                throw new Exception("stream_select() timeout : ".json_encode($streamPool)." after {$timeout} seconds");
            } else {
                throw new Exception("stream_select() failed : ".json_encode($streamPool));
            }
        }

        $timeout && usleep($timeout);

        return 0;
    }
}
