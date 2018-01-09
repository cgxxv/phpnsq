<?php

namespace OkStuff\PHPNSQ;

class Config
{
    public $initialized = false;

    public $dialTimeout = 1;
    public $readTimeout = [
        'default' => 60,
        'min' => 0.1,
        'max' => 5*60,
    ];
    public $writeTimeout = [
        'default' => 1,
        'min' => 0.1,
        'max' => 5*60,
    ];
    public $localAddr;

    public $lookupdPollInterval = [
        'default' => 60,
        'min' => 0.01,
        'max' => 5*60,
    ];
    public $lookupdPollJitter = [
        'default' => 0.3,
        'min' => 0,
        'max' => 1,
    ];

    public $maxRequeueDelay = [
        'default' => 15*60,
        'min' => 0,
        'max' => 60*60,
    ];
    public $defaultRequeueDelay = [
        'default' => 90,
        'min' => 0,
        'max' => 60*60,
    ];

    //TODO: need to be fixed
    public $backoffStrategy;
    public $maxBackoffDuration = [
        'default' => 2*60,
        'min' => 0,
        'max' => 60*60,
    ];
    public $backoffMultiplier = [
        'default' => 1,
        'min' => 0,
        'max' => 60*60,
    ];

    public $maxAttempts = [
        'default' => 5,
        'min' => 0,
        'max' => 65535,
    ];
    
    public $lowRdyIdleTimeout = [
        'default' => 10,
        'min' => 1,
        'max' => 5*60,
    ];
    public $lowRdyTimeout = [
        'default' => 30,
        'min' => 1,
        'max' => 5*60,
    ];
    public $rdyRedistributeInterval = [
        'default' => 5,
        'min' => 0.001,
        'max' => 5,
    ];

    public $clientID;
    public $hostname;
    public $userAgent;

    public $heartbeatInterval = 30;
    //TODO:
    public $sampleRate = [
        'min' => 0,
        'max' => 99,
    ];

    public $tlsV1 = true;
    //TODO:
    public $tlsConfig;

    //TODO:
    public $deflate = true;
    public $deflateLevel = [
        'default' => 6,
        'min' => 1,
        'max' => 9,
    ];
    public $snappy = true;

    public $outputBufferSize = 16384;
    public $outputBufferTimeout = 0.25;

    public $maxInFlight = [
        'default' => 1,
        'min' => 0,
    ];
    //TODO:
    public $msgTimeout = [
        'min' => 0,
    ];

    public $authSecret;

    public function __construct()
    {
        $this->initialized = true;
    }

    public function set($key, $val)
    {
        if (isset($this->$key))
        {
            if (is_array($this->$key))
            {
                $this->$key['default'] = $val;                
            } else {
                $this->$key = $val;
            }

            return true;
        }

        return false;
    }

    //check if all the value is between min and max value.
    public function validate()
    {
        foreach ($this as $key => $val)
        {
            if (is_array($val) && count($val) == 3)
            {
                if (!isset($val['default']) || !isset($val['min']) || !isset($val['max']))
                {
                    throw new Exception(sprintf("invalid %s value", $key));
                }

                if ($val['default'] < $val['min'])
                {
                    throw new Exception(sprintf("invalid %s ! %v < %v", $key, $val['default'], $val['min']));
                }

                if ($val['default'] > $val['max'])
                {
                    throw new Exception(sprintf("invalid %s ! %v > %v", $key, $val['default'], $val['min']));
                }
            }
        }
    }
}