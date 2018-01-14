<?php

namespace OkStuff\PhpNsq;

use Closure;
use Exception;
use OkStuff\PhpNsq\Command\Base as SubscribeCommand;
use OkStuff\PhpNsq\Message\Message;
use OkStuff\PhpNsq\Tunnel\Config;
use OkStuff\PhpNsq\Tunnel\Tunnel;
use OkStuff\PhpNsq\Wire\Reader;
use OkStuff\PhpNsq\Wire\Writer;

class PhpNsq
{
    private $nsqdPool = [];
    private $writer;
    private $reader;

    private $channel;
    private $topic;

    public function __construct($nsq)
    {
        $this->writer = new Writer();
        $this->reader = new Reader();

        foreach ($nsq["nsq"]["nsqd-addrs"] as $value) {
            $addr = explode(":", $value);
            array_push($this->nsqdPool, new Tunnel(
                new Config($addr[0], $addr[1])
            ));
        }
    }

    public function getAllNsqds()
    {
        return $this->nsqdPool;
    }

    public function getOneNsqd()
    {
        $pool = $this->nsqdPool;
        if (count($pool) <= 0) {
            throw new Exception("empty nsqd pool");
        }

        return $pool[array_rand($pool)];
    }

    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    public function setTopic($topic)
    {
        $this->topic = $topic;

        return $this;
    }

    public function publish(Message $message)
    {
        $this->getOneNsqd()->write(
            $this->writer->pub($this->topic, json_encode($message->getBody()))
        );

        return $this;
    }

    public function subscribe(SubscribeCommand $cmd, Closure $callback)
    {
        $tunnel = $this->getOneNsqd();

        $cmd->addReadStream($tunnel->socket, function ($socket) use ($tunnel, $callback) {
            $this->handleMessage($tunnel, $callback);
        });

        $tunnel->write($this->writer->sub($this->topic, $this->channel))
            ->write($this->writer->rdy(1));
    }

    public function handleMessage(Tunnel $tunnel, $callback)
    {
        $reader = $this->reader->bindTunnel($tunnel);

        if ($reader->isHeartbeat()) {
            $tunnel->write($this->writer->nop());
        } elseif ($reader->isMessage()) {
            $message = $reader->getMessage();

            try {
                call_user_func($callback, $message);
            } catch (Exception $e) {
                throw new Exception($e);
            }

            $tunnel->write($this->writer->fin($message->getId()));
            $tunnel->write($this->writer->rdy(1));

        } elseif ($reader->isOk()) {
            echo sprintf("Ignoring \"OK\" frame in SUB loop\n");
        } else {
            throw new Exception("Error/unexpected frame received: " . json_encode($reader));
        }
    }
}
