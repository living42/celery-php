<?php
namespace Celery;

use Celery\Exception\TaskError;
use Celery\Exception\TaskRevokedError;

class AsyncResult
{
    protected $taskid;

    protected $resultBackend;

    public function __construct($taskid, $resultBackend)
    {
        $this->taskid = $taskid;
        $this->resultBackend = $resultBackend;
    }

    public function getTaskId()
    {
        return $this->taskid;
    }

    public function get($timeout = null)
    {
        $meta = $this->resultBackend->waitForPending($this->taskid, $timeout);

        if (in_array($meta['status'], Task::PROPAGATE_STATES)) {
            if ($meta['status'] == 'REVOKED') {
                throw new TaskRevokedError($meta['result']);
            }
            throw new TaskError($meta['result'], $meta['traceback']);
        }
        return $meta['result'];
    }
}
