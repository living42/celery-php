<?php
namespace Celery\Exception;

class TaskError extends CeleryException
{
    protected $result;

    public function __construct($result)
    {
        $this->result = $result;
        $message = $result->exc_message;
        if (is_array($message)) {
            $message = implode(', ', $message);
        }
        parent::__construct("{$result->exc_type}: {$message}");
    }
}
