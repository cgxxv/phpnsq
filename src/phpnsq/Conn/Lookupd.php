<?php
namespace OkStuff\PhpNsq\Conn;

use Exception;
use OkStuff\PhpNsq\Stream\Socket;

class Lookupd
{
    const lookupTopicUri = "http://%s:%d/lookup?topic=%s";

    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getProducers(string $topic)
    {
        $defaults = array(
            CURLOPT_URL => sprintf(self::lookupTopicUri, $this->config->host, $this->config->port, $topic),
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 4
        );
      
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        if( ! $result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);

        $nsqdConns = [];

        $d = json_decode($result, true);
        foreach ($d["producers"] as $producer) {
            array_push($nsqdConns, $this->connectProducer($producer));
        }

        return $nsqdConns;
    }

    private function connectProducer($producer)
    {
        $config = new Config($producer["broadcast_address"], $producer["tcp_port"]);
        $config->set("tlsConfig", $this->config->get("tlsConfig"))
            ->set("authSwitch", $this->config->get("authSwitch"))
            ->set("authSecret", $this->config->get("authSecret"));
        return new Nsqd($config);
    }
}
