<?php
namespace Celery;

class Message
{
    protected $headers;

    protected $properties;

    protected $body;

    protected $exchange;

    protected $routingKey;

    protected $priority;

    public function __construct($headers, $properties, $body, $exchange, $routingKey, $priority)
    {
        $this->headers = $headers;
        $this->properties = $properties;
        $this->body = $body;
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->priority = $priority;
    }

    public function getExchange()
    {
        return $this->exchange;
    }

    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function toArray()
    {
        return [
            'content-type'=>'application/json',
            'content-encoding'=>'utf-8',
            'headers'=>$this->headers,
            'properties'=>array_merge(
                $this->properties,
                ['body_encoding'=>'base64']
            ),
            'body'=>base64_encode(json_encode($this->body)),
        ];
    }
}
