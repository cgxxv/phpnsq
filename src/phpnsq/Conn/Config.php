<?php

namespace OkStuff\PhpNsq\Conn;

use Exception;

class Config
{
    public $host;
    public $port;

    private $clientTimeout = 30;

    private $readTimeout  = [
        'default' => 60,
        'min'     => 0.1,
        'max'     => 5 * 60,
    ];
    private $writeTimeout = [
        'default' => 1,
        'min'     => 0.1,
        'max'     => 5 * 60,
    ];

    //TODO: need to be fixed
    private $backoffStrategy;
    private $maxBackoffDuration = [
        'default' => 2 * 60,
        'min'     => 0,
        'max'     => 60 * 60,
    ];
    private $backoffMultiplier  = [
        'default' => 1,
        'min'     => 0,
        'max'     => 60 * 60,
    ];

    private $maxAttempts = [
        'default' => 5,
        'min'     => 0,
        'max'     => 65535,
    ];

    private $heartbeatInterval = 30;

    private $tlsConfig;

    private $blocking = true;

    private $authSwitch = false;

    private $authSecret = "";
    
    private $logdir = "";

    public function __construct($host = "", $port = 0)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function set($key, $val)
    {
        if (is_array($this->$key)) {
            $this->$key['default'] = $val;
        } else {
            $this->$key = $val;
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
