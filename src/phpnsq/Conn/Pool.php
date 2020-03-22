<?php

namespace OkStuff\PhpNsq\Conn;

class Pool
{
    private $pool = [];

    public function __construct($nsq)
    {
        foreach ($nsq["nsq"]["nsqd-addrs"] as $value) {
            $addr = explode(":", $value);
            $this->addConn(new Conn(
                new Config($addr[0], $addr[1])
            ));
        }
    }

    public function addConn(Conn $conn)
    {
        array_push($this->pool, $conn);

        return $this;
    }

    public function getConn()
    {
        return $this->pool[array_rand($this->pool)];
    }
}
