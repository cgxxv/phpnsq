<?php

namespace OkStuff\PhpNsq\Tunnel;

class Pool
{
    private $pool = [];

    public function __construct($nsq)
    {
        foreach ($nsq["nsq"]["nsqd-addrs"] as $value) {
            $addr = explode(":", $value);
            $this->addTunnel(new Tunnel(
                new Config($addr[0], $addr[1])
            ));
        }
    }

    public function addTunnel(Tunnel $tunnel)
    {
        array_push($this->pool, $tunnel);

        return $this;
    }

    public function getTunnel()
    {
        return $this->pool[array_rand($this->pool)];
    }
}
