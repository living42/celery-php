<?php
namespace Celery;

use Predis\Client;
use Predis\Connection\ConnectionException;
use Celery\Exception\TimeoutError;

class RedisResultBackend
{
    protected $taskKeyPrefix = 'celery-task-meta-';

    protected $url;

    protected $client;

    public function __construct($url)
    {
        $this->url = $url;

        $this->client = $this->createClient($url);
    }

    public function getTaskMeta($taskid)
    {
        $meta = $this->client->get($this->getKeyForTask($taskid));
        if (! $meta) {
            return ['status'=>'PENDING', 'result'=>null];
        }
        return $this->decode($meta);
    }

    public function getKeyForTask($taskid)
    {
        return $this->taskKeyPrefix.$taskid;
    }

    public function waitForPending($taskid, $timeout = null)
    {
        $opts = ['read_write_timeout'=>$timeout];
        $pubsub = $this->createClient(
            $this->url,
            $timeout ? ['read_write_timeout'=>$timeout] : []
        )->pubsubLoop();

        $pubsub->subscribe($this->getKeyForTask($taskid));

        while (true) {
            $meta = $this->getTaskMeta($taskid);
            if (in_array($meta['status'], Task::READY_STATES)) {
                return $meta;
            }
            try {
                foreach ($pubsub as $message) {
                    if ($message->kind === 'message') {
                        $meta = $this->decode($message->payload);
                        if (in_array($meta['status'], Task::READY_STATES)) {
                            return $meta;
                        }
                    }
                }
            } catch (ConnectionException $e) {
                if (strstr($e->getMessage(), "Error while reading line from the server")) {
                    throw new TimeoutError('The operation timed out.');
                }
                throw $e;
            }
        }
    }

    protected function decode($payload)
    {
        return json_decode($payload, JSON_OBJECT_AS_ARRAY);
    }

    protected function createClient($url, $options = [])
    {
        $parsedUrl = parse_url($url);
        return new Client(array_merge([
            'scheme'=>'tcp',
            'host'=>$parsedUrl['host'],
            'port'=>isset($parsedUrl['port']) ? $parsedUrl['port'] : 6379
        ], $options));
    }
}
