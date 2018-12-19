<?php

require_once __DIR__ . '/../vendor/autoload.php';


$states = ['created', 'paid', 'done', 'shipped', 'cancelled'];

$transitions = [
    [
        'name' => 'pay',
        'from' => ['created'],
        'to' => 'paid',
    ], [
        'name' => 'process',
        'from' => ['paid'],
        'to' => 'done',
    ], [
        'name' => 'ship',
        'from' => ['done'],
        'to' => 'shipped',
    ], [
        'name' => 'cancel',
        'from' => ['created', 'paid'],
        'to' => 'cancelled',
        'listeners' => [
            'after' => function (\Hbliang\FiniteStateMachine\Event\TransitionEvent $event) {
                // Email to customer
            }
        ]
    ]
];

class Model
{
    public $state;

    public function save()
    {
        // save to db
    }
}

$model = new Model();
$model->state = 'created';
$stateMachine = new \Hbliang\FiniteStateMachine\StateMachine(
    $model,
    new \Symfony\Component\EventDispatcher\EventDispatcher(),
    function (Model $model, \Hbliang\FiniteStateMachine\Contracts\StateInterface $state) {
        $model->state = $state->getName();
    }
);

foreach ($states as $state) {
    $stateMachine->addState(new \Hbliang\FiniteStateMachine\State($state));
}

foreach ($transitions as $transition) {
    $stateMachine->addTransition(new \Hbliang\FiniteStateMachine\Transition(
        $transition['name'],
        $transition['from'],
        $transition['to'],
        $transition['listener'] ?? []
    ));
}

$stateMachine->initialize($model->state);
echo 'current state: ' . $model->state . PHP_EOL;
echo 'apply pay' . PHP_EOL;
$stateMachine->apply('pay');
echo 'current state: ' . $model->state . PHP_EOL;
echo 'Can apply cancel? ' . ($stateMachine->can('cancel') ? 'yes' : 'no') . PHP_EOL;
echo 'apply cancel' . PHP_EOL;
$stateMachine->apply('cancel');
echo 'current state: ' . $model->state . PHP_EOL;
