<?php

namespace OkStuff\PhpNsq\Conn;

class Pool
{
    private $pool = [];

    public function __construct($nsq)
    {
        foreach ($nsq["nsq"]["nsqd-addrs"] as $value) {
            $addr = explode(":", $value);
            $config = new Config($addr[0], $addr[1]);
            if (!empty($nsq["nsq"]["tls_config"])) {
                $config->set("tlsConfig", $nsq["nsq"]["tls_config"]);
            }
            $this->addConn(new Conn($config));
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
