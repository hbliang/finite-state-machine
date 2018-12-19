<?php


namespace Hbliang\FiniteStateMachine\Test\Laravel;


use Hbliang\FiniteStateMachine\Laravel\Stateful;
use Hbliang\FiniteStateMachine\Laravel\StatefulInterface;
use Hbliang\FiniteStateMachine\StateMachine;
use PHPUnit\Framework\TestCase;

class StatefulTest extends TestCase
{
    public function testStateMachine()
    {
        $mock = $this->getMockForAbstractClass(StatefulForTest::class);
        $mock->state = null;

        $mock->expects($this->once())
            ->method('getStatePropertyName')
            ->willReturn('state');

        $mock->expects($this->once())
            ->method('getStates')
            ->willReturn(['created', 'done']);

        $mock->expects($this->once())
            ->method('getTransitions')
            ->willReturn([
                'process' => [
                    'from' => ['created'],
                    'to' => 'done',
                ]
            ]);

        $stateMachine = $mock->stateMachine();

        $this->assertInstanceOf(StateMachine::class, $stateMachine);
    }
}

abstract class StatefulForTest implements StatefulInterface
{
    use Stateful;
}