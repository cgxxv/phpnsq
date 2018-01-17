<?php

namespace OkStuff\PhpNsq\Wire;

class Writer
{
    const MAGIC_V2 = "  V2";

    public static function magic()
    {
        return self::MAGIC_V2;
    }

    public static function pub($topic, $data)
    {
        $cmd  = self::command("PUB", $topic);
        $size = pack("N", strlen($data));

        return $cmd . $size . $data;
    }

    public static function sub($topic, $channel)
    {
        return self::command("SUB", $topic, $channel);
    }

    public static function rdy($count)
    {
        return self::command("RDY", $count);
    }

    public static function nop()
    {
        return self::command("NOP");
    }

    public static function fin($id)
    {
        return self::command("FIN", $id);
    }

    public static function req($id, $timeout)
    {
        return self::command("REQ", $id, $timeout);
    }

    public static function identify()
    {
        return self::command("IDENTIFY");
    }

    private static function command($action, ...$params)
    {
        return sprintf("%s %s%s", $action, implode(' ', $params), "\n");
    }
}
