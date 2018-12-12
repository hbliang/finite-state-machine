<?php


namespace Hbliang\FiniteStateMachine\Contracts;


interface TransitionListenerInterface
{
    public function handle();
}