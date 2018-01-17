<?php

namespace OkStuff\PhpNsq\Wire;

use OkStuff\PhpNsq\Utility\IntPacker;

class Writer
{
    const MAGIC_V2 = "  V2";

    public static function magic()
    {
        return self::MAGIC_V2;
    }

    //TODO:
    public static function identify()
    {
        return self::command("IDENTIFY");
    }

    public static function pub($topic, $body)
    {
        $cmd  = self::command("PUB", $topic);
        $size = IntPacker::uInt32(strlen($body), true);

        return $cmd . $size . $body;
    }

    public static function mpub($topic, array $bodies)
    {
        $cmd  = self::command("MPUB", $topic);
        $num  = IntPacker::uInt32(count($bodies), true);
        $mb   = implode(array_map(function ($body) {
            return IntPacker::uint32(strlen($body), true) . $body;
        }, $bodies));
        $size = IntPacker::uInt32(strlen($num . $mb), true);

        return $cmd . $size . $num . $mb;
    }

    public static function dpub($topic, $deferTime, $body)
    {
        $cmd  = self::command("DPUB", $topic, $deferTime);
        $size = IntPacker::uInt32(strlen($body), true);

        return $cmd . $size . $body;
    }

    public static function sub($topic, $channel)
    {
        return self::command("SUB", $topic, $channel);
    }

    public static function rdy($count)
    {
        return self::command("RDY", $count);
    }

    public static function fin($id)
    {
        return self::command("FIN", $id);
    }

    public static function req($id, $timeout)
    {
        return self::command("REQ", $id, $timeout);
    }

    public static function touch($id)
    {
        return self::command("TOUCH", $id);
    }

    //TODO: should optimize use this command
    public static function cls()
    {
        return self::command("CLS");
    }

    public static function nop()
    {
        return self::command("NOP");
    }

    //TODO:
    public static function auth($secret)
    {
        $cmd  = self::command("AUTH");
        $size = IntPacker::uInt32(strlen($secret), true);

        return $cmd . $size . $secret;
    }

    private static function command($action, ...$params)
    {
        return sprintf("%s %s%s", $action, implode(' ', $params), "\n");
    }
}
