<?php
namespace Celery;

use Predis\Client;
use Celery\Exception\TimeoutError;

class RedisResultBackend
{
    protected $taskKeyPrefix = 'celery-task-meta-';

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

    public function getTaskMeta($taskid)
    {
        $meta = $this->client->get($this->getKeyForTask($taskid));
        if (! $meta) {
            return ['status'=>'PENDING', 'result'=>null];
        }
        return json_decode($meta, JSON_OBJECT_AS_ARRAY);
    }

    public function getKeyForTask($taskid)
    {
        return $this->taskKeyPrefix.$taskid;
    }

    public function waitForPending($taskid, $timeout = null, $interval = 0.5)
    {
        $elapsed = 0;
        while (true) {
            $meta = $this->getTaskMeta($taskid);
            if (in_array($meta['status'], Task::READY_STATES)) {
                return $meta;
            }
            sleep($interval);
            $elapsed += $interval;
            if ($timeout && $elapsed >= $timeout) {
                throw new TimeoutError('The operation timed out.');
            }
        };
    }
}
