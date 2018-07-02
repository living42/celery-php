<?php
namespace Celery;

use DateTime;
use DateTimeZone;
use Exception;
use Ramsey\Uuid\Uuid;

abstract class Task
{

    const READY_STATES = ['SUCCESS', 'FAILURE', 'REVOKED'];

    const PROPAGATE_STATES = ['FAILURE', 'REVOKED'];

    protected $name;

    protected $taskid;

    protected $args = [];

    protected $kwargs = [];

    protected $exchange = '';

    protected $routingKey = 'celery';

    protected $priority = 0;

    protected $eta;

    protected $countdown;

    protected $expires;

    protected $timeLimit;

    protected $softTimeLimit;

    public function __construct($params = [])
    {
        if (! $this->name) {
            throw new Exception("name of this task is not defined");
        }
        $this->taskid = (string)Uuid::uuid4();

        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $this->args[] = $value;
            } else {
                $this->kwargs[$key] = $value;
            }
        }
    }

    public function priority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    public function routingKey($routingKey)
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    public function expires($expires)
    {
        $this->expires = $expires;
        return $this;
    }

    public function eta(DateTime $eta)
    {
        $this->eta = $eta;
        return $this;
    }

    public function countdown($countdown)
    {
        $this->countdown = $countdown;
        return $this;
    }

    public function timeLimit($timeLimit)
    {
        $this->timeLimit = $timeLimit;
        return $this;
    }

    public function softTimeLimit($softTimeLimit)
    {
        $this->softTimeLimit = $softTimeLimit;
        return $this;
    }

    public function options($options)
    {
        if (isset($options['priority'])) {
            $this->priority($options['priority']);
        }
        if (isset($options['routing_key'])) {
            $this->routingKey($options['routing_key']);
        }
        if (isset($options['expires'])) {
            $this->expires($options['expires']);
        }
        if (isset($options['eta'])) {
            $this->eta($options['eta']);
        }
        if (isset($options['countdown'])) {
            $this->countdown($options['countdown']);
        }
        if (isset($options['time_limit'])) {
            $this->timeLimit($options['time_limit']);
        }
        if (isset($options['soft_time_limit'])) {
            $this->softTimeLimit($options['soft_time_limit']);
        }

        return $this;
    }

    public function setTaskId($taskid)
    {
        $this->taskid = $taskid;
    }

    public function getTaskId()
    {
        return $this->taskid;
    }

    public function toMessage($celery)
    {
        $headers = [
            'lang'=>'py',
            'task'=>$this->name,
            'id'=>$this->taskid,
            'shadow'=>null,
            'eta'=>$this->resolveEta($celery->getTimezone()),
            'expires'=>$this->resolveExpires($celery->getTimezone()),
            'group'=>null,
            'retries'=>0,
            'timelimit'=>[$this->timeLimit, $this->softTimeLimit],
            'root_id'=>$this->taskid,
            'parent_id'=>null,
            'argsrepr'=>json_encode($this->args),
            'kwargsrepr'=>json_encode((object)$this->kwargs),
            'origin'=>'php/'.gethostname(),
        ];

        $properties = [
            'correlation_id'=>$this->taskid,
            'reply_to'=>'',
            'delivery_mode'=>2,
            'delivery_info'=>[
                'exchange'=>$this->exchange,
                'routing_key'=>$this->routingKey,
                'priority'=>$this->priority,
            ],
            'delivery_tag'=>(string)Uuid::uuid4(),
        ];

        $body = [
            $this->args,
            (object)$this->kwargs,
            [
                'callbacks'=>null,
                'errbacks'=>null,
                'chain'=>null,
                'chord'=>null,
            ],
        ];

        return new Message(
            $headers,
            $properties,
            $body,
            $this->exchange,
            $this->routingKey,
            $this->priority
        );
    }

    protected function resolveExpires($timezone)
    {
        $expires = $this->expires;
        if (is_null($expires)) {
            return $expires;
        }
        if (is_int($expires)) {
            $now = (new DateTime('now', new DateTimeZone($timezone)));
            $expires = $now->modify("+ {$expires} seconds");
        }
        if (! is_string($expires)) {
            $expires = $expires->format('Y-m-d\TH:i:s.v');
        }
        return $expires;
    }

    protected function resolveEta($timezone)
    {
        if ($this->countdown) {
            $eta = (new DateTime('now', new DateTimeZone($timezone)))
                ->modify("+ {$this->countdown} seconds");
            return $eta->format('Y-m-d\TH:i:s.v');
        } elseif ($this->eta) {
            if (! is_string($this->eta)) {
                return $this->eta->format('Y-m-d\TH:i:s.v');
            }
        }
        return null;
    }
}
