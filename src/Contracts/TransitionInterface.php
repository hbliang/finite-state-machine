<?php


namespace Hbliang\FiniteStateMachine\Contracts;


interface TransitionInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getFromStates();

    /**
     * Get State Name
     *
     * @return string
     */
    public function getToState();

    public function getBeforeTransitionEventName();

    public function getAfterTransitionEventName();

    public function getBeforeTransitionListener();

    public function getAfterTransitionListener();
}