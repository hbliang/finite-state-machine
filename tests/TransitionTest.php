<?php


namespace Hbliang\FiniteStateMachine\Test;


use Hbliang\FiniteStateMachine\Contracts\TransitionListenerInterface;
use Hbliang\FiniteStateMachine\Transition;
use PHPUnit\Framework\TestCase;

class TransitionTest extends TestCase
{
    public function testGetBeforeTransitionListener()
    {
        $transition = new Transition('process', ['created'], 'processed');
        $this->assertNull($transition->getBeforeTransitionListener());

        $before = $this->createMock(TransitionListenerInterface::class);
        $transition = new Transition('process', ['created'], 'processed', ['before' => $before]);
        $this->assertEquals($before, $transition->getBeforeTransitionListener());
    }

    public function testGetAfterTransitionListener()
    {
        $transition = new Transition('process', ['created'], 'processed');
        $this->assertNull($transition->getAfterTransitionListener());

        $after = $this->createMock(TransitionListenerInterface::class);
        $transition = new Transition('process', ['created'], 'processed', ['after' => $after]);
        $this->assertEquals($after, $transition->getAfterTransitionListener());
    }

    public function testBeforeListener()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Transition before listener process must implements TransitionListenerInterface");

        $listeners = [
            'before' => new \stdClass(),
        ];
        $transition = new Transition('process', ['created'], 'processed', $listeners);
    }

    public function testAfterListener()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Transition after listener process must implements TransitionListenerInterface");

        $listeners = [
            'after' => new \stdClass(),
        ];
        $transition = new Transition('process', ['created'], 'processed', $listeners);
    }

    public function testListener()
    {
        $after = $this->createMock(TransitionListenerInterface::class);
        $before = $this->createMock(TransitionListenerInterface::class);
        $listeners = [
            'after' => $after,
            'before' => $before,
        ];

        $transition = new Transition('process', ['created'], 'processed', $listeners);

        $this->assertEquals($before, $transition->getAfterTransitionListener());
    }
}