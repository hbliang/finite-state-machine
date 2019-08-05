<?php


namespace Hbliang\FiniteStateMachine\Test\Laravel;


use Hbliang\FiniteStateMachine\Contracts\StateInterface;
use Hbliang\FiniteStateMachine\Laravel\Stateful;
use Hbliang\FiniteStateMachine\Laravel\StatefulInterface;
use Hbliang\FiniteStateMachine\StateMachine;
use Illuminate\Database\Eloquent\Model;
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
            ->willReturn(['created', 'done' => StateInterface::TYPE_FINAL]);

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

        $this->assertEquals('created', $mock->getCurrentState());

        $stateMachine->apply('process');

        $this->assertEquals('done', $stateMachine->getCurrentState()->getName());
        $this->assertTrue($stateMachine->getCurrentState()->isFinal());
    }

    public function testStateMachine1()
    {
        $mock = $this->getMockForAbstractClass(StatefulForTest::class);
        $mock->state = null;

        $mock->expects($this->once())
            ->method('getStatePropertyName')
            ->willReturn('state');

        $mock->expects($this->once())
            ->method('getStates')
            ->willReturn(['created', 'processed', 'done' => StateInterface::TYPE_FINAL]);

        $mock->expects($this->once())
            ->method('getTransitions')
            ->willReturn([
                'process' => [
                    'from' => ['created'],
                    'to' => 'processed',
                ]
            ]);

        $stateMachine = $mock->stateMachine();

        $this->assertInstanceOf(StateMachine::class, $stateMachine);

        $this->assertEquals('created', $mock->getCurrentState());

        $stateMachine->apply('process');

        $this->assertEquals('processed', $stateMachine->getCurrentState()->getName());
    }

    public function testInitialStateStateMachine()
    {
        $mock = $this->getMockForAbstractClass(StatefulForTest::class);
        $mock->state = null;

        $mock->expects($this->once())
            ->method('getStatePropertyName')
            ->willReturn('state');

        $mock->expects($this->once())
            ->method('getStates')
            ->willReturn(['done' => StateInterface::TYPE_FINAL, 'created' => StateInterface::TYPE_INITIAL]);

        $mock->expects($this->once())
            ->method('getTransitions')
            ->willReturn([
                'process' => [
                    'from' => ['created'],
                    'to' => 'done',
                ]
            ]);

        $stateMachine = $mock->stateMachine();

        $this->assertEquals('created', $mock->getCurrentState());
        $this->assertTrue($stateMachine->getCurrentState()->isInitial());
        $this->assertEquals('created', $stateMachine->getInitialState()->getName());
    }

    public function testEmptyStatesStateMachine()
    {
        $mock = $this->getMockForAbstractClass(StatefulForTest::class);
        $mock->state = null;

        $mock->expects($this->once())
            ->method('getStatePropertyName')
            ->willReturn('state');

        $mock->expects($this->once())
            ->method('getStates')
            ->willReturn([]);

        $mock->expects($this->once())
            ->method('getTransitions')
            ->willReturn([]);

        $stateMachine = $mock->stateMachine();

        $this->assertNull($mock->getCurrentState());
    }
}

abstract class StatefulForTest extends Model implements StatefulInterface
{
    use Stateful;
}