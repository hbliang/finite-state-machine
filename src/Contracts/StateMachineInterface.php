<?php


namespace Hbliang\FiniteStateMachine\Contracts;


interface StateMachineInterface
{
    /**
     * @param string $initialStateName
     */
    public function initialize($initialStateName);

    /**
     * @param TransitionInterface $transition
     * @return mixed
     */
    public function addTransition(TransitionInterface $transition);

    /**
     * @param StateInterface $state
     * @return mixed
     */
    public function addState(StateInterface $state);

    /**
     * @param string|TransitionInterface $transition
     * @return mixed
     */
    public function apply($transition);

    /**
     * @param string|TransitionInterface $transition
     * @return boolean
     */
    public function can($transition);

    /**
     * @return StateInterface
     */
    public function getCurrentState();

    /**
     * @return array
     */
    public function getAvailableTransitions();

    public function getHost();
}