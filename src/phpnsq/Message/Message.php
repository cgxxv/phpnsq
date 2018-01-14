<?php

namespace OkStuff\PhpNsq\Message;

class Message
{
    private $decoded = false;
    private $type;
    private $size;
    private $id;
    private $body;
    private $timestamp;
    private $attempts;
    private $nsqdAddr;
    private $delegate;
    private $response;
    private $error;

    public function __construct()
    {
        $this->timestamp = microtime(true);
    }

    /**
     * @return bool
     */
    public function isDecoded()
    {
        return $this->decoded;
    }

    public function setDecoded()
    {
        $this->decoded = true;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp = null)
    {
        if (null === $timestamp) {
            $this->timestamp = microtime(true);
        }
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @param mixed $attempts
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNsqdAddr()
    {
        return $this->nsqdAddr;
    }

    /**
     * @param mixed $nsqdAddr
     */
    public function setNsqdAddr($nsqdAddr)
    {
        $this->nsqdAddr = $nsqdAddr;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDelegate()
    {
        return $this->delegate;
    }

    /**
     * @param mixed $delegate
     */
    public function setDelegate($delegate)
    {
        $this->delegate = $delegate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }
}
