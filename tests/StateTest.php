<?php


namespace Hbliang\FiniteStateMachine\Test;


use Hbliang\FiniteStateMachine\State;
use Hbliang\FiniteStateMachine\Transition;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    public function testAddTransition()
    {
        $state = new State('test');
        $this->assertEquals([], $state->getTransitions());
        $transition = new Transition('test', ['test'], 'done');
        $state->addTransition($transition);
        $this->assertEquals($transition, $state->getTransitions()['test']);

        $transition = new Transition('ship', ['ship'], 'done');
        $state->addTransition($transition);

        $this->assertEquals($transition, $state->getTransitions()['ship']);


    }

    public function testCan()
    {
        $state = new State('test');
        $transition = new Transition('ship', ['ship'], 'done');

        $state->addTransition($transition);
        $this->assertTrue($state->can('ship'));
        $this->assertFalse($state->can('test'));
    }
}