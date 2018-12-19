<?php


namespace Hbliang\FiniteStateMachine;


use Hbliang\FiniteStateMachine\Contracts\TransitionInterface;
use Hbliang\FiniteStateMachine\Contracts\TransitionListenerInterface;

class Transition implements TransitionInterface
{
    /**
     * @var array
     */
    protected $from;
    /**
     * @var string
     */
    protected $to;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $listeners;

    public function __construct($name, $from, $to, $listeners = [])
    {
        $this->name = $name;
        $this->from = is_array($from) ? $from : [$from];
        $this->to = $to;
        $this->listeners = $listeners;


        if (isset($this->listeners['before']) && !is_callable($this->listeners['before']) && !in_array(TransitionListenerInterface::class, class_implements($this->listeners['before']))) {
            throw new \UnexpectedValueException("Transition before listener {$this->name} must implements TransitionListenerInterface");
        }

        if (isset($this->listeners['after']) && !is_callable($this->listeners['after']) && !in_array(TransitionListenerInterface::class, class_implements($this->listeners['after']))) {
            throw new \UnexpectedValueException("Transition after listener {$this->name} must implements TransitionListenerInterface");
        }
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getFromStates()
    {
        return $this->from;
    }

    /**
     * Get State Name
     *
     * @return string
     */
    public function getToState()
    {
        return $this->to;
    }

    public function getBeforeTransitionListener()
    {
        return $this->listeners['before'] ?? null;
    }

    public function getAfterTransitionListener()
    {
        return $this->listeners['after'] ?? null;
    }

    public function getBeforeTransitionEventName()
    {
        return "FSM.BeforeTransition.{$this->getName()}";
    }

    public function getAfterTransitionEventName()
    {
        return "FSM.AfterTransition.{$this->getName()}";
    }
}