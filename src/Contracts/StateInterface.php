<?php


namespace Hbliang\FiniteStateMachine\Contracts;


interface StateInterface
{
    const TYPE_INITIAL = 'State.Type.Initial';
    const TYPE_NORMAL = 'State.Type.Normal';
    const TYPE_FINAL = 'State.Type.Final';

    /**
     * @return string
     */
    public function getName();

    /**
     * @return boolean
     */
    public function isInitial();

    /**
     * @return boolean
     */
    public function isFinal();

    /**
     * @return boolean
     */
    public function isNormal();

    /**
     * @return string
     */
    public function getType();

    /**
     * Return the available transitions
     *
     * @return array
     */
    public function getTransitions();

    /**
     * @param TransitionInterface|string $transition
     * @return boolean
     */
    public function can($transition);
}