<?php

namespace OkStuff\PhpNsq\Wire;

class Writer
{
    const MAGIC_V2 = "  V2";

    public function magic()
    {
        return self::MAGIC_V2;
    }

    public function pub($topic, $data)
    {
        $cmd = $this->command("PUB", $topic);
        $size = pack("N", strlen($data));

        return $cmd.$size.$data;
    }

    public function sub($topic, $channel)
    {
        return $this->command("SUB", $topic, $channel);
    }

    public function rdy($count)
    {
        return $this->command("RDY", $count);
    }

    public function nop()
    {
        return $this->command("NOP");
    }

    public function fin($id)
    {
        return $this->command("FIN", $id);
    }

    public function req($id, $timeout)
    {
        return $this->command("REQ", $id, $timeout);
    }

    private function command($action, ...$params)
    {
        return sprintf("%s %s%s", $action, implode(' ', $params), "\n");
    }
}
