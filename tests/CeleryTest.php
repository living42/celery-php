<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use DateTime;
use DateTimeZone;
use Celery\Celery;
use Celery\AsyncResult;
use Celery\Exception\TaskError;
use Celery\Exception\TaskRevokedError;
use Celery\Exception\TimeoutError;
use Tests\Task\Greet;
use Tests\Task\Bad;

class CeleryTest extends TestCase
{
    protected $celery;

    protected function setUp()
    {
        $this->celery = new Celery([
            'broker'=>'redis://localhost',
            'backend'=>'redis://localhost',
        ]);
    }

    public function testDispatchTaskWithArgs()
    {
        foreach ([
            [],
            ['world'],
            ['name'=>'world'],
            ['world', 'hello'=>'你好']
        ] as $args) {
            $asyncResult = $this->celery->dispatch(new Greet($args));
            $this->assertInstanceOf(AsyncResult::class, $asyncResult);
        }
    }

    public function testGetResult()
    {
        $result = $this->celery->dispatch(new Greet())->get();
        $this->assertNotEmpty($result);
    }

    public function testDispatchWithOptions()
    {
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                (new Greet())->options(['expires'=>30])
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                (new Greet())->options(['expires'=>30])
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                (new Greet())->expires(30)
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                (new Greet())->expires((new DateTime('now', new DateTimeZone('UTC')))->modify('+ 30 seconds'))
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                (new Greet())->eta((new DateTime('now', new DateTimeZone('UTC')))->modify('+ 3 seconds'))
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                (new Greet())->countdown(3)
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                (new Greet())->priority(9)
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                (new Greet())->routingKey('default')
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                // hard time limit
                (new Greet())->timeLimit(10)
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                // soft time limit
                (new Greet())->softTimeLimit(5)
            )
        );
        $this->assertInstanceOf(
            AsyncResult::class,
            $this->celery->dispatch(
                // soft time limit
                (new Greet())->softTimeLimit(5)
            )
        );
        // $celery->dispatch(
        //     (new Greet())->compression('gzip')
        // );

        // $celery->dispatch(
        //     (new Greet())->immutable(true)
        // );

        // $celery->dispatch(
        //     new Celery\Chain([
        //         new Greet(),
        //         new Greet(),
        //     ])
        // );

        // $celery->dispatch(
        //     new Celery\Chain([
        //         Greet::s(),
        //         Greet::s(),
        //     ])
        // );

        // $celery->dispatch(
        //     new Celery\Chain([
        //         Greet::subtask(),
        //         Greet::subtask(),
        //     ])
        // );

        // $celery->dispatch(
        //     new Celery\Chain([
        //         Greet::si(),
        //         Greet::si(),
        //     ])
        // );

        // $celery->dispatch(
        //     new Celery\Chain([
        //         Greet::subtask()->immutable(),
        //         Greet::subtask()->immutable(),
        //     ])
        // );

        // $celery->dispatch(
        //     new Celery\Chain([
        //         Greet::subtask()->immutable(),
        //         Greet::subtask()->immutable(),
        //     ])
        // );

        // $celery->dispatch(
        //     new Celery\Group([
        //         Greet::s(),
        //         Greet::s(),
        //     ])
        // );

        // $celery->dispatch(
        //     new Celery\Chord(
        //         [new Greet(), new Greet()],
        //         new Greet()
        //     )
        // );
    }

    public function testThrowTaskError()
    {
        $this->expectException(TaskError::class);

        $this->celery->dispatch(new Bad())->get();
    }

    public function testThrowRevokedError()
    {
        $this->expectException(TaskRevokedError::class);

        $task = new Greet();
        $this->celery->dispatch($task->expires(1)->countdown(2))->get();
    }

    public function testThrowTimeoutError()
    {
        $this->expectException(TimeoutError::class);

        $task = new Greet();
        $this->celery->dispatch($task->countdown(2))->get(1);
    }
}
