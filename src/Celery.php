<?php
namespace Celery;

use Exception;

class Celery
{
    protected $globalApp;

    protected $brokerUrl = "redis://localhost";

    protected $resultBackendUrl;

    protected $timezone = 'UTC';

    protected $broker;

    protected $resultBackend;

    public function __construct($options = [])
    {
        if (isset($options['broker'])) {
            $this->brokerUrl = $options['broker'];
        }

        if (parse_url($this->brokerUrl, PHP_URL_SCHEME) !== 'redis') {
            throw new Exception('currently only support redis as broker');
        }

        $this->setupBroker();

        if (isset($options['backend'])) {
            $this->resultBackendUrl = $options['backend'];
            $this->setupResultBackend();
        }

        if (isset($options['timezone'])) {
            $this->timezone = $options['timezone'];
        }
    }

    public function dispatch(Task $task)
    {
        $message = $task->toMessage($this);
        $this->broker->publish($message);
        return $this->createAsyncResult($task->getTaskId());
    }

    public function createAsyncResult($taskid)
    {
        return new AsyncResult($taskid, $this->resultBackend);
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function getResultBackend()
    {
        return $this->resultBackend;
    }

    protected function setupBroker()
    {
        $this->broker = new RedisBroker($this->brokerUrl);
    }

    protected function setupResultBackend()
    {
        $this->resultBackend = new RedisResultBackend($this->resultBackendUrl);
    }
}
