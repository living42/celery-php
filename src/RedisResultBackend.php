<?php
namespace Celery;

use Predis\Client;

class RedisResultBackend
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
}
