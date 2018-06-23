<?php
namespace Celery;

use Predis\Client;

class RedisBroker
{
    protected $client;

    public function __construct($url)
    {
        $parsedUrl = parse_url($url);

        $this->client = new Client([
            'scheme'=>'tcp',
            'host'=>$parsedUrl['host'],
            'port'=>isset($parsedUrl['port']) ? $parsedUrl['port'] : 6379
        ]);
    }

    public function publish(Message $message)
    {
        $key = $this->queueKey($message->getRoutingKey(), $message->getPriority());
        $this->client->lpush($key, json_encode($message->toArray()));
    }

    protected function queueKey($routingKey, $priority = null)
    {
        if ($priority) {
            return "{$routingKey}\x06\x16{$priority}";
        } else {
            return $routingKey;
        }
    }
}
