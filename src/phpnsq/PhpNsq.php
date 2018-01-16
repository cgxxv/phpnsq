<?php

namespace OkStuff\PhpNsq;

use Closure;
use Exception;
use OkStuff\PhpNsq\Command\Base as SubscribeCommand;
use OkStuff\PhpNsq\Message\Message;
use OkStuff\PhpNsq\Tunnel\Config;
use OkStuff\PhpNsq\Tunnel\Tunnel;
use OkStuff\PhpNsq\Utility\Logging;
use OkStuff\PhpNsq\Wire\Reader;
use OkStuff\PhpNsq\Wire\Writer;

class PhpNsq
{
    private $nsqdPool = [];
    private $writer;
    private $reader;
    private $logger;

    private $channel;
    private $topic;

    public function __construct($nsq)
    {
        $this->writer = new Writer();
        $this->reader = new Reader();
        $this->logger = new Logging("PHPNSQ", __DIR__."/../../tmp");

        foreach ($nsq["nsq"]["nsqd-addrs"] as $value) {
            $addr = explode(":", $value);
            array_push($this->nsqdPool, new Tunnel(
                new Config($addr[0], $addr[1])
            ));
        }
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getAllNsqds()
    {
        return $this->nsqdPool;
    }

    public function getOneNsqd()
    {
        $pool = $this->nsqdPool;
        if (count($pool) <= 0) {
            $this->logger->error("empty nsqd pool");
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
        try {
            $this->getOneNsqd()->write(
                $this->writer->pub($this->topic, json_encode($message->getBody()))
            );
        } catch (Exception $e) {
            $this->logger->error("publish error", $e);
        }
    }

    public function subscribe(SubscribeCommand $cmd, Closure $callback)
    {
        try {
            $tunnel = $this->getOneNsqd();
            $sock   = $tunnel->getSock();

            $cmd->addReadStream($sock, function ($sock) use ($tunnel, $callback) {
                $this->handleMessage($tunnel, $callback);
            });

            $tunnel->write($this->writer->sub($this->topic, $this->channel))
                ->write($this->writer->rdy(1));
        } catch (Exception $e) {
            $this->logger->error("subscribe error", $e);
        }
    }

    public function handleMessage(Tunnel $tunnel, $callback)
    {
        $reader = $this->reader->bindTunnel($tunnel)->bindFrame();

        if ($reader->isHeartbeat()) {
            $tunnel->write($this->writer->nop());
        } elseif ($reader->isMessage()) {
            $msg = $reader->getMessage();
            if (null === $msg) {
                return;
            }

            try {
                call_user_func($callback, $msg);
            } catch (Exception $e) {
                $this->logger->error("call user func error", $e->getMessage());
            }

            $tunnel->write($this->writer->fin($msg->getId()));
            $tunnel->write($this->writer->rdy(1));
        } elseif ($reader->isOk()) {
            $this->logger->info('Ignoring "OK" frame in SUB loop');
        } else {
            $this->logger->error("Error/unexpected frame received: ", $reader);
        }
    }
}
