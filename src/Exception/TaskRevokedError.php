<?php
namespace Celery\Exception;

class TaskRevokedError extends TaskError
{
    public function __construct($result)
    {
        parent::__construct($result['exc_message'][0]);
    }
}
