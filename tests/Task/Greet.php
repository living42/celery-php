<?php
namespace Tests\Task;

use Celery\Task;

class Greet extends Task
{
    protected $name = 'app.greet';
}
