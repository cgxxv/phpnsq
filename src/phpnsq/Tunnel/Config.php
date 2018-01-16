<?php

namespace OkStuff\PhpNsq\Tunnel;

use Exception;

class Config
{
    public $host;
    public $port;

    public $initialized = false;

    private $dialTimeout = 1;
    private $readTimeout = [
        'default' => 60,
        'min' => 0.1,
        'max' => 5*60,
    ];
    private $writeTimeout = [
        'default' => 1,
        'min' => 0.1,
        'max' => 5*60,
    ];
    private $localAddr;

    private $lookupdPollInterval = [
        'default' => 60,
        'min' => 0.01,
        'max' => 5*60,
    ];
    private $lookupdPollJitter = [
        'default' => 0.3,
        'min' => 0,
        'max' => 1,
    ];

    private $maxRequeueDelay = [
        'default' => 15*60,
        'min' => 0,
        'max' => 60*60,
    ];
    private $defaultRequeueDelay = [
        'default' => 90,
        'min' => 0,
        'max' => 60*60,
    ];

    //TODO: need to be fixed
    private $backoffStrategy;
    private $maxBackoffDuration = [
        'default' => 2*60,
        'min' => 0,
        'max' => 60*60,
    ];
    private $backoffMultiplier = [
        'default' => 1,
        'min' => 0,
        'max' => 60*60,
    ];

    private $maxAttempts = [
        'default' => 5,
        'min' => 0,
        'max' => 65535,
    ];
    
    private $lowRdyIdleTimeout = [
        'default' => 10,
        'min' => 1,
        'max' => 5*60,
    ];
    private $lowRdyTimeout = [
        'default' => 30,
        'min' => 1,
        'max' => 5*60,
    ];
    private $rdyRedistributeInterval = [
        'default' => 5,
        'min' => 0.001,
        'max' => 5,
    ];

    private $clientID;
    private $hostname;
    private $userAgent;

    private $heartbeatInterval = 30;
    private $sampleRate = [
        'min' => 0,
        'max' => 99,
    ];

    private $tlsV1 = true;
    //TODO:
    private $tlsConfig;

    //TODO:
    private $deflate = true;
    private $deflateLevel = [
        'default' => 6,
        'min' => 1,
        'max' => 9,
    ];
    private $snappy = true;

    private $outputBufferSize = 16384;
    private $outputBufferTimeout = 0.25;

    private $maxInFlight = [
        'default' => 1,
        'min' => 0,
    ];
    private $msgTimeout = [
        'min' => 0,
    ];

    private $authSecret;

    private $blocking = true;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->initialized = true;
    }

    public function set($key, $val)
    {
        if (isset($this->$key)) {
            if (is_array($this->$key)) {
                $this->$key['default'] = $val;                
            } else {
                $this->$key = $val;
            }
        }

        return $this;
    }

    public function get($key)
    {
        return $this->$key;
    }

    //check if all the value is between min and max value.
    public function validate()
    {
        foreach ($this as $key => $val) {
            if (is_array($val) && count($val) == 3) {
                if (!isset($val['default']) || !isset($val['min']) || !isset($val['max'])) {
                    throw new Exception(sprintf("invalid %s value", $key));
                }

                if ($val['default'] < $val['min']) {
                    throw new Exception(sprintf("invalid %s ! %v(default) < %v(min)", $key, $val['default'], $val['min']));
                }

                if ($val['default'] > $val['max']) {
                    throw new Exception(sprintf("invalid %s ! %v(default) > %v(max)", $key, $val['default'], $val['max']));
                }
            }
        }

        return true;
    }
}
