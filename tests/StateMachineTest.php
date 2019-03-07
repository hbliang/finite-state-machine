<?php


namespace Hbliang\FiniteStateMachine\Test;


use Hbliang\FiniteStateMachine\Contracts\TransitionListenerInterface;
use Hbliang\FiniteStateMachine\Event\TransitionEvent;
use Hbliang\FiniteStateMachine\Exceptions\DenyTransitionException;
use Hbliang\FiniteStateMachine\State;
use Hbliang\FiniteStateMachine\StateMachine;
use Hbliang\FiniteStateMachine\Transition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

class StateMachineTest extends TestCase
{
    public function testInitializeWithInvalidStateName()
    {
        $this->expectException(\UnexpectedValueException::class);
        $stateMachine = new StateMachine(null, new EventDispatcher());
        $stateMachine->initialize('what');
    }

    public function testInitialize()
    {
        $state = new State('created');
        $stateMachine = new StateMachine(null, new EventDispatcher());
        $stateMachine->addState($state);
        $stateMachine->initialize('created');
        $this->assertEquals($state, $stateMachine->getCurrentState());
    }

    public function testAddTransition()
    {
        $listener = $this->createMock(TransitionListenerInterface::class);
        $state = new State('created');
        $closure = function () {

        };
        $transition = new Transition('process', ['created'], 'processed', [
            'before' => $listener,
            'after' => $closure,
        ]);

        $eventDispatcher = new EventDispatcher();
        $stateMachine = new StateMachine(null, $eventDispatcher);
        $stateMachine->addState($state);
        $stateMachine->addTransition($transition);

        $this->assertEquals($transition, $stateMachine->getTransitions()['process']);
        $this->assertEquals($transition, $state->getTransitions()['process']);


        $this->assertTrue($eventDispatcher->hasListeners($transition->getBeforeTransitionEventName()));
        $this->assertTrue($eventDispatcher->hasListeners($transition->getAfterTransitionEventName()));
    }

    public function testCan()
    {
        $state = new State('created');
        $transition = new Transition('process', ['created'], 'processed');
        $stateMachine = new StateMachine(null, new EventDispatcher());
        $stateMachine->addState($state);
        $stateMachine->addTransition($transition);
        $stateMachine->initialize('created');
        $this->assertTrue($stateMachine->can('process'));
        $this->assertFalse($stateMachine->can('hi'));
    }

    public function testGetAvailableTransitions()
    {
        $state = new State('created');
        $stateMachine = new StateMachine(null, new EventDispatcher());
        $stateMachine->addState($state);
        $transition = new Transition('process', ['created'], 'processed');
        $stateMachine->addTransition($transition);
        $stateMachine->initialize('created');

        $this->assertEquals([$transition->getName() => $transition], $stateMachine->getAvailableTransitions());
    }


    public function testDenyApply()
    {
        $this->expectException(DenyTransitionException::class);
        $stateMachine = new StateMachine(null, new EventDispatcher());

        $stateMachine->addState(new State('created'));
        $stateMachine->initialize('created');

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Transition hello does not found");
        $stateMachine->apply('hello');
    }


    public function testApply()
    {

        $traceableEventDispatcher = new TraceableEventDispatcher(
            new EventDispatcher(),
            new Stopwatch()
        );

        $stateMachine = new StateMachine(null, $traceableEventDispatcher);
        $stateMachine->addState(new State('created'));
        $stateMachine->addState(new State('done'));

        $before = $this->createMock(TransitionListenerInterface::class);
        $after = $this->createMock(TransitionListenerInterface::class);
        $transition = new Transition(
            'process',
            ['created'],
            'done',
            ['before' => $before, 'after' => $after]
        );
        $stateMachine->addTransition($transition);
        $stateMachine->addListener(TransitionEvent::PRE_TRANSITION, function () {
        });
        $stateMachine->addListener(TransitionEvent::POST_TRANSITION, function () {
        });
        $stateMachine->initialize('created');

        $this->assertEquals('created', $stateMachine->getCurrentState()->getName());

        $this->assertEquals(0, count($traceableEventDispatcher->getCalledListeners()));
        $stateMachine->apply('process');

        $calledListeners = $traceableEventDispatcher->getCalledListeners();
        $this->assertEquals(4, count($calledListeners));

        $eventNames = array_column($calledListeners, 'event');

        $this->assertTrue(in_array(TransitionEvent::PRE_TRANSITION, $eventNames));
        $this->assertTrue(in_array(TransitionEvent::POST_TRANSITION, $eventNames));
        $this->assertTrue(in_array($transition->getBeforeTransitionEventName(), $eventNames));
        $this->assertTrue(in_array($transition->getAfterTransitionEventName(), $eventNames));
        $this->assertEquals('done', $stateMachine->getCurrentState()->getName());
    }


}