<?php
namespace Celery\Exception;

class TaskError extends CeleryException
{
    protected $traceback;

    public function __construct($message, $traceback = null)
    {
        parent::__construct($message);
        $this->$traceback = $traceback;
    }
}
