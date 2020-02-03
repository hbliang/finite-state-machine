<?php


namespace Hbliang\FiniteStateMachine;


use Hbliang\FiniteStateMachine\Contracts\StateInterface;
use Hbliang\FiniteStateMachine\Contracts\StateMachineInterface;
use Hbliang\FiniteStateMachine\Contracts\TransitionInterface;
use Hbliang\FiniteStateMachine\Event\TransitionEvent;
use Hbliang\FiniteStateMachine\Exceptions\DenyTransitionException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StateMachine implements StateMachineInterface
{

    /**
     * host which state machine is bind to
     * @var Object
     */
    protected $host;

    /**
     * trigger when change state
     * @var callable|null
     */
    protected $stateHandler;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var StateInterface
     */
    protected $currentState;

    /**
     * @var array
     */
    protected $states = [];

    /**
     * @var array
     */
    protected $transitions = [];

    public function __construct(
        $host,
        EventDispatcherInterface $dispatcher,
        $updateStateHandler = null
    )
    {
        $this->host = $host;
        $this->stateHandler = $updateStateHandler;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string $initialStateName
     */
    public function initialize($initialStateName)
    {
        $state = $this->getState($initialStateName);
        if ($state === null) {
            throw new \UnexpectedValueException("can't find {$initialStateName} in states");
        }

        $this->currentState = $state;
    }

    /**
     * @param TransitionInterface $transition
     * @return mixed
     */
    public function addTransition(TransitionInterface $transition)
    {
        $this->transitions[$transition->getName()] = $transition;
        foreach ($transition->getFromStates() as $stateName) {
            if ($state = $this->getState($stateName)) {
                $state->addTransition($transition);
            }
        }

        if ($listener = $transition->getBeforeTransitionListener()) {
            $this->dispatcher->addListener($transition->getBeforeTransitionEventName(), is_callable($listener) ? $listener : [new $listener, 'handle']);
        }

        if ($listener = $transition->getAfterTransitionListener()) {
            $this->dispatcher->addListener($transition->getAfterTransitionEventName(), is_callable($listener) ? $listener : [new $listener, 'handle']);
        }

        return $this;
    }

    /**
     * @param StateInterface $state
     * @return mixed
     */
    public function addState(StateInterface $state)
    {
        $this->states[$state->getName()] = $state;
        return $this;
    }

    /**
     * @param string|TransitionInterface $transition
     * @return mixed
     */
    public function apply($transition)
    {
        if (is_string($transition)) {
            if (!isset($this->transitions[$transition])) {
                throw new \UnexpectedValueException("Transition $transition does not found");
            }
            $transition = $this->transitions[$transition];
        }

        if (!$this->can($transition)) {
            throw new DenyTransitionException(sprintf(
                "Current State %s can't make Transition %s",
                $this->currentState->getName(),
                $transition instanceof TransitionInterface ? $transition->getName() : $transition
            ));
        }

        $transitionEvent = new TransitionEvent($transition, $this->currentState, $this);

        $this->dispatcher->dispatch($transitionEvent, TransitionEvent::PRE_TRANSITION);
        $this->dispatcher->dispatch($transitionEvent, $transition->getBeforeTransitionEventName());

        $this->setCurrentState($transition->getToState());

        $this->dispatcher->dispatch($transitionEvent, $transition->getAfterTransitionEventName());
        $this->dispatcher->dispatch($transitionEvent, TransitionEvent::POST_TRANSITION);

    }

    /**
     * @param string|TransitionInterface $transition
     * @return boolean
     */
    public function can($transition)
    {
        return $this->currentState->can($transition);
    }

    /**
     * @return StateInterface
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    protected function setCurrentState($state)
    {
        if ($state instanceof StateInterface) {
            if (!in_array($state, $this->states, true)) {
                throw new \UnexpectedValueException("can't find object {$state->getName()} in states");
            }
        } elseif (is_string($state)) {
            $state = $this->getState($state);
            if ($state === null) {
                throw new \UnexpectedValueException("can't find {$state} in states");
            }
        } else {
            throw new \UnexpectedValueException("Method setCurrentState only accept string or StateInterface.");
        }

        $this->currentState = $state;
        $this->stateHandler && call_user_func_array($this->stateHandler, [$this->host, $state]);

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableTransitions()
    {
        return $this->currentState->getTransitions();
    }

    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return array
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * @param $stateName
     * @return State|null
     */
    public function getState($stateName)
    {
        return $this->states[$stateName] ?? null;
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    public function getInitialState()
    {
        /** @var StateInterface $state */
        foreach ($this->states as $state) {
            if ($state->isInitial()) {
                return $state;
            }
        }

        return null;
    }
}