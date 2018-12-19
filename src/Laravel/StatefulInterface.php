<?php


namespace Hbliang\FiniteStateMachine\Laravel;


interface StatefulInterface
{
    /**
     * @return array
     */
    public function getStates();

    /**
     * @return array
     */
    public function getTransitions();

    /**
     * Get state property name in database
     *
     * @return string
     */
    public function getStatePropertyName();
}