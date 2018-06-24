<?php
require __DIR__.'/../vendor/autoload.php';

class Greet extends Celery\Task
{
    protected $name = 'app.greet';
}

$celery = new Celery\Celery([
    'broker'=>'redis://localhost',
    'backend'=>'redis://localhost',
]);

$celery->dispatch(new Greet());
# with potitional arguments
$celery->dispatch(new Greet(['world']));
# with keyword arguments
$celery->dispatch(new Greet(['name'=>'world']));
# combine them together
$celery->dispatch(new Greet(['world', 'hello'=>'你好']));

$celery->dispatch(new Greet())->get();

$celery->dispatch(
    (new Greet())->options(['expires'=>30])
);

$celery->dispatch(
    (new Greet())->expires(30)
);

$celery->dispatch(
    (new Greet())->expires((new DateTime('now', new DateTimeZone('UTC')))->modify('+ 30 seconds'))
);

$celery->dispatch(
    (new Greet())->eta((new DateTime('now', new DateTimeZone('UTC')))->modify('+ 3 seconds'))
);

$celery->dispatch(
    (new Greet())->countdown(3)
);

$celery->dispatch(
    (new Greet())->priority(9)
);

$celery->dispatch(
    (new Greet())->routingKey('default')
);

// hard time limit
$celery->dispatch(
    (new Greet())->timeLimit(10)
);

// soft time limit
$celery->dispatch(
    (new Greet())->softTimeLimit(5)
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
