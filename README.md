# A Simple Finite State Machine
![build-status](https://travis-ci.org/hbliang/finite-state-machine.svg?branch=master)

## Features

- Integration with Laravel


## Requirements

- PHP >= 7.0


## Code Examples

A simple state flow chart in a order：  

```
Order:

         pay          process           ship
created ------> paid ----------> done ----------> shipped
   |             |
   |    cancel   |
   |______ ______|
          ↓
      cancelled 
```

```PHP
<?php

require_once 'vendor/autoload.php';

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
        // update your state inside your object after transition.
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
```


In Laravel, it is assumed that Model `Order` have state property. Let make `Order` integrated with `StateMachine`.
First, Order have to implement `Hbliang\FiniteStateMachine\Laravel\StatefulInterface` and use `Hbliang\FiniteStateMachine\Laravel` trait.
Then, you can apply transition by method like `$order->transition('transition')` and check whether can make a transition by method like `$order->canTransition('transition')`
Look at example `Order.php` below:
```PHP
<?php

namespace App;

use Hbliang\FiniteStateMachine\Contracts\TransitionListenerInterface;
use Hbliang\FiniteStateMachine\Event\TransitionEvent;
use Hbliang\FiniteStateMachine\Laravel\Stateful;
use Hbliang\FiniteStateMachine\Laravel\StatefulInterface;
use Hbliang\FiniteStateMachine\Contracts\StateInterface;
use Illuminate\Database\Eloquent\Model;

class Order extends Model implements StatefulInterface
{
    use Stateful;

    protected $fillable = ['state'];

    /**
     * The state of which you don't declare type explicitly will be seen as normal state.
     * If there is not explicitly declared initial state, 
     * state machine will take the first state of list as initial state.
     *   
     * @return array
     */
    public function getStates()
    {
        return [
            'created' => StateInterface::TYPE_INITIAL,
            'paid',
            'done',
            'cancelled' => StateInterface::TYPE_FINAL,
            'shipped',
        ];
        
        /**
         return [
            'created', // initial state set by state machine automatically 
            'paid',
            'done',
            'cancelled' => StateInterface::TYPE_FINAL,
            'shipped',
         ];
        */
    }

    /**
     * @return array
     */
    public function getTransitions()
    {
        return [
            'pay' => [
                'from' => ['created'],
                'to' => 'paid',
            ],
            'process' => [
                'from' => ['paid'],
                'to' => 'done',
                'listeners' => [
                    'after' => function (TransitionEvent $event) {
                        // Email to customer
                    }
                ]

            ],
            'ship' => [
                'from' => ['done'],
                'to' => 'shipped',
            ],
            'cancel' => [
                'from' => ['created', 'paid'],
                'to' => 'cancelled',
                'listeners' => [
                    'after' => CancelledListener::class
                ],
            ]
        ];

    }

    /**
     * Get state property name in database
     *
     * @return string
     */
    public function getStatePropertyName()
    {
        return 'state';
    }
}

class CancelledListener implements TransitionListenerInterface
{ 

    public function handle()
    {
        echo 'Cancelled' . PHP_EOL;
    }
}
```

It is easy to transition. Like 

```PHP
$order = new App\Order;
// if you don't check whether you can transition, it is likely to throw a exception when you actually can't transition. 
if ($order->canTransition('cancel')) {
    $order->transition('cancel');
}
$order->save();

```

- Don't directly set state property like `$order->state = 'cancelled'`. It is a extremely dangerous. The only way you can update state is to use method `transition()`;
- Don't forget to set default state in database. For example in migration, `$table->string('state', 20)->default('created');`

