<?php

namespace OkStuff\PhpNsq\Conn;

class Pool
{
    private $pool = [];
    private $lookupdPool = [];

    public function __construct(array $nsq, bool $lookupd)
    {
        if ($lookupd) {
            foreach ($nsq["nsq"]["lookupd_addrs"] as $value) {
                $addr = explode(":", $value);
                $config = new Config($addr[0], $addr[1]);
                $config->set("authSwitch", $nsq["nsq"]["auth_switch"])
                    ->set("authSecret", $nsq["nsq"]["auth_secret"])
                    ->set("logdir", $nsq["nsq"]["logdir"]);
                if (!empty($nsq["nsq"]["tls_config"])) {
                    $config->set("tlsConfig", $nsq["nsq"]["tls_config"]);
                }
                $this->addLookupd(new Lookupd($config));
            }
        } else {
            foreach ($nsq["nsq"]["nsqd_addrs"] as $value) {
                $addr = explode(":", $value);
                $config = new Config($addr[0], $addr[1]);
                $config->set("authSwitch", $nsq["nsq"]["auth_switch"])
                    ->set("authSecret", $nsq["nsq"]["auth_secret"])
                    ->set("logdir", $nsq["nsq"]["logdir"]);
                if (!empty($nsq["nsq"]["tls_config"])) {
                    $config->set("tlsConfig", $nsq["nsq"]["tls_config"]);
                }
                $this->addConn(new Nsqd($config));
            }
        }
    }

    public function addConn(Nsqd ...$conns)
    {
        foreach ($conns as $conn) {
            array_push($this->pool, $conn);
        }

        return $this;
    }

    public function getConn()
    {
        return $this->pool[array_rand($this->pool)];
    }

    public function addNsqdByLookupd(Lookupd $conn, string $topic)
    {
        $nsqdConns = $conn->getProducers($topic);
        $this->addConn(...$nsqdConns);

        return $this;
    }

    public function addLookupd(Lookupd $conn)
    {
        array_push($this->lookupdPool, $conn);

        return $this;
    }

    public function getLookupd()
    {
        return $this->lookupdPool[array_rand($this->lookupdPool)];
    }

    public function getLookupdCount()
    {
        return count($this->lookupdPool);
    }
}
