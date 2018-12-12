<?php


namespace Hbliang\FiniteStateMachine\Event;


use Hbliang\FiniteStateMachine\Contracts\StateInterface;
use Hbliang\FiniteStateMachine\Contracts\StateMachineInterface;
use Hbliang\FiniteStateMachine\Contracts\TransitionInterface;
use Symfony\Component\EventDispatcher\Event;

class TransitionEvent extends Event
{
    const PRE_TRANSITION = 'FSM.PRE_TRANSITION';
    const POST_TRANSITION = 'FSM.POST_TRANSITION';
    /**
     * @var TransitionInterface
     */
    protected $transition;
    /**
     * @var StateInterface
     */
    protected $fromState;
    /**
     * @var StateMachineInterface
     */
    protected $stateMachine;

    public function __construct(TransitionInterface $transition, StateInterface $fromState, StateMachineInterface $stateMachine)
    {
        $this->transition = $transition;
        $this->fromState = $fromState;
        $this->stateMachine = $stateMachine;
    }

    /**
     * @return TransitionInterface
     */
    public function getTransition()
    {
        return $this->transition;
    }

    /**
     * @return StateInterface
     */
    public function getFromState()
    {
        return $this->fromState;
    }

    /**
     * @return StateMachineInterface
     */
    public function getStateMachine()
    {
        return $this->stateMachine;
    }
}