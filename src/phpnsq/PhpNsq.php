<?php

namespace OkStuff\PhpNsq;

use Closure;
use Exception;
use OkStuff\PhpNsq\Command\Base as SubscribeCommand;
use OkStuff\PhpNsq\Tunnel\Pool;
use OkStuff\PhpNsq\Tunnel\Tunnel;
use OkStuff\PhpNsq\Utility\Logging;
use OkStuff\PhpNsq\Wire\Reader;
use OkStuff\PhpNsq\Wire\Writer;

class PhpNsq
{
    private $pool;
    private $logger;
    private $channel;
    private $topic;
    private $reader;

    public function __construct($nsq)
    {
        $this->reader = new reader();
        $this->logger = new Logging("PHPNSQ", $nsq["nsq"]["logdir"]);
        $this->pool   = new Pool($nsq);
    }

    public function getLogger()
    {
        return $this->logger;
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

    public function publish($message)
    {
        try {
            $tunnel = $this->pool->getTunnel();
            $tunnel->write(Writer::pub($this->topic, $message));
        } catch (Exception $e) {
            $this->logger->error("publish error", $e);
        }
    }

    public function publishMulti(...$bodies)
    {
        try {
            $tunnel = $this->pool->getTunnel();
            $tunnel->write(Writer::mpub($this->topic, $bodies));
        } catch (Exception $e) {
            $this->logger->error("publish error", $e);
        }
    }

    public function publishDefer($message, $deferTime)
    {
        try {
            $tunnel = $this->pool->getTunnel();
            $tunnel->write(Writer::dpub($this->topic, $deferTime, $message));
        } catch (Exception $e) {
            $this->logger->error("publish error", $e);
        }
    }

    public function subscribe(SubscribeCommand $cmd, Closure $callback)
    {
        try {
            $tunnel = $this->pool->getTunnel();
            $sock   = $tunnel->getSock();

            $cmd->addReadStream($sock, function ($sock) use ($tunnel, $callback) {
                $this->handleMessage($tunnel, $callback);
            });

            $tunnel->write(Writer::sub($this->topic, $this->channel))->write(Writer::rdy(1));
        } catch (Exception $e) {
            $this->logger->error("subscribe error", $e);
        }
    }

    protected function handleMessage(Tunnel $tunnel, $callback)
    {
        $reader = $this->reader->bindTunnel($tunnel)->bindFrame();

        if ($reader->isHeartbeat()) {
            $tunnel->write(Writer::nop());
        } elseif ($reader->isMessage()) {

            $msg = $reader->getMessage();
            try {
                call_user_func($callback, $msg);
            } catch (Exception $e) {
                $this->logger->error("Will be requeued: ", $e->getMessage());

                $tunnel->write(Writer::touch($msg->getId()))
                    ->write(Writer::req(
                        $msg->getId(),
                        $tunnel->getConfig()->get("defaultRequeueDelay")["default"]
                    ));
            }

            $tunnel->write(Writer::fin($msg->getId()))
                ->write(Writer::rdy(1));
        } elseif ($reader->isOk()) {
            $this->logger->info('Ignoring "OK" frame in SUB loop');
        } else {
            $this->logger->error("Error/unexpected frame received: ", $reader);
        }
    }
}
