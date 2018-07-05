<?php
namespace Tests\Task;

use Celery\Task;

class Bad extends Task
{
    protected $name = 'app.bad';
}
