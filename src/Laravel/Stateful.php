<?php


namespace Hbliang\FiniteStateMachine\Laravel;


use Hbliang\FiniteStateMachine\Contracts\StateInterface;
use Hbliang\FiniteStateMachine\State;
use Hbliang\FiniteStateMachine\StateMachine;
use Hbliang\FiniteStateMachine\Transition;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait Stateful
{
    /**
     * @var StateMachine
     */
    protected $stateMachine;

    public function stateMachine()
    {
        if (!$this->stateMachine) {
            $stateName = $this->getStatePropertyName();
            $this->stateMachine = new StateMachine($this, new EventDispatcher(), function(Model $model, StateInterface $state) use ($stateName) {
                $model->{$stateName} = $state->getName();
            });

            foreach ($this->getStates() as $state) {
                $this->stateMachine->addState(new State($state));
            }
            foreach ($this->getTransitions() as $transitionName => $transition) {
                $this->stateMachine->addTransition(new Transition(
                    $transitionName,
                    $transition['from'],
                    $transition['to'],
                    $transition['listeners'] ?? []
                ));
            }

            // State is null while first time to use state machine
            $this->{$stateName} && $this->stateMachine->initialize($this->{$stateName});
        }
        return $this->stateMachine;
    }

    public function canTransition($transition)
    {
        return $this->stateMachine()->can($transition);
    }

    public function transition($transition)
    {
        $this->stateMachine()->apply($transition);
    }

    public function getCurrentState()
    {
        return $this->stateMachine()->getCurrentState()->getName();
    }
}