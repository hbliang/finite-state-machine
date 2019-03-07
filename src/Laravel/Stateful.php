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

            $states = $this->getStates();

            foreach ($states as $state => $type) {
                $state = $state ?: $type;

                if (!in_array($type , [StateInterface::TYPE_INITIAL, StateInterface::TYPE_NORMAL, StateInterface::TYPE_FINAL])) {
                    $type = StateInterface::TYPE_NORMAL;
                }

                $this->stateMachine->addState(new State($state, $type));
            }

            if (count($states) > 0 && !$this->stateMachine->getInitialState()) {
                $state = reset($states);
                $this->stateMachine->getState($state)->setAsInitial();
            }

            foreach ($this->getTransitions() as $transitionName => $transition) {
                $this->stateMachine->addTransition(new Transition(
                    $transitionName,
                    $transition['from'],
                    $transition['to'],
                    $transition['listeners'] ?? []
                ));
            }

            if ($this->{$stateName}) {
                $this->stateMachine->initialize($this->{$stateName});
            } elseif ($state = $this->stateMachine->getInitialState()) {
                $this->stateMachine->initialize($state->getName());
            }
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
        if ($state = $this->stateMachine()->getCurrentState()) {
            return $state->getName();
        }
        return null;
    }
}