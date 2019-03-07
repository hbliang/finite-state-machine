<?php


namespace Hbliang\FiniteStateMachine;


use Hbliang\FiniteStateMachine\Contracts\StateInterface;
use Hbliang\FiniteStateMachine\Contracts\TransitionInterface;

class State implements StateInterface
{

    protected $name;
    protected $type;
    /**
     * @var array
     */
    protected $transitions;

    public function __construct($name, $type = self::TYPE_NORMAL, $transitions = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->transitions = $transitions;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isInitial()
    {
        return $this->type === self::TYPE_INITIAL;
    }

    /**
     * @return boolean
     */
    public function isFinal()
    {
        return $this->type === self::TYPE_FINAL;
    }

    /**
     * @return boolean
     */
    public function isNormal()
    {
        return $this->type === self::TYPE_NORMAL;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function setAsInitial()
    {
        $this->type = self::TYPE_INITIAL;
        return $this;
    }

    public function setAsNormal()
    {
        $this->type = self::TYPE_NORMAL;
        return $this;
    }

    public function setAsFinal()
    {
        $this->type = self::TYPE_FINAL;
        return $this;
    }

    /**
     * Return the available transitions
     *
     * @return array
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * @param TransitionInterface $transition
     */
    public function addTransition(TransitionInterface $transition)
    {
        $this->transitions[$transition->getName()] = $transition;
    }

    /**
     * @param TransitionInterface $transition
     * @return boolean
     */
    public function can($transition)
    {
        if ($this->isFinal()) {
            return false;
        }

        if ($transition instanceof TransitionInterface) {
            return in_array($transition, $this->transitions);
        }  elseif(is_string($transition)) {
            return isset($this->transitions[$transition]);
        } else {
            return false;
        }
    }
}