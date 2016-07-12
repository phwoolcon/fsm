<?php
namespace Phwoolcon\Fsm\Tests\Unit;

use PHPUnit_Framework_TestCase;
use Phwoolcon\Fsm\Exception;
use Phwoolcon\Fsm\StateMachine;

class StateMachineTest extends PHPUnit_Framework_TestCase
{

    public function testInit()
    {
        $fsm = StateMachine::create([
            $state = 'foo' => [
                'process' => 'bar',
            ],
        ]);
        $this->assertEquals($state, $fsm->getCurrentState());
    }

    public function testInitWithHistory()
    {
        $fsm = StateMachine::create([
            'foo' => [
                $action = 'process' => $state = 'bar',
            ],
        ], $history = [
            [
                'action' => $action,
                'state' => $state,
            ],
        ]);
        $this->assertEquals($state, $fsm->getCurrentState());
        $this->assertEquals($history, $fsm->getHistory());
    }

    public function testCanDoAction()
    {
        $fsm = StateMachine::create([
            $start = 'foo' => [
                'process' => $to = 'bar',
            ],
        ]);
        $this->assertEquals($start, $fsm->getCurrentState());
        $this->assertTrue($fsm->canDoAction('process'));
        $this->assertFalse($fsm->canDoAction('invalid'));
    }

    public function testDoAction()
    {
        $fsm = StateMachine::create([
            $start = 'foo' => [
                'process' => $to = 'bar',
            ],
        ]);
        $this->assertEquals($start, $fsm->getCurrentState());
        $fsm->doAction('process');
        $this->assertEquals($to, $fsm->getCurrentState());
    }

    public function testDoActionWithClosure()
    {
        $to = 'bar';
        $fsm = StateMachine::create([
            $start = 'foo' => [
                'process' => function () use ($to) {
                    return $to;
                },
            ],
        ]);
        $this->assertEquals($start, $fsm->getCurrentState());
        $fsm->doAction('process');
        $this->assertEquals($to, $fsm->getCurrentState());
    }

    public function testDoInvalidAction()
    {
        $fsm = StateMachine::create([
            $start = 'foo' => [
                'process' => $to = 'bar',
            ],
        ]);
        $this->assertEquals($start, $fsm->getCurrentState());
        $e = null;
        try {
            $fsm->doAction('invalid');
        } catch (Exception $e) {
        }
        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals(Exception::INVALID_ACTION, $e->getCode());
    }

    public function testNextAction()
    {
        $fsm = StateMachine::create([
            $start = 'foo' => [
                'process1' => $step1 = 'bar',
            ],
            $step1 => [
                'process2' => $step2 = 'hello',
            ],
        ]);
        $this->assertEquals($start, $fsm->getCurrentState());
        $fsm->nextAction();
        $this->assertEquals($step1, $fsm->getCurrentState());
        $fsm->nextAction();
        $this->assertEquals($step2, $fsm->getCurrentState());
    }

    public function testInvalidNextAction()
    {
        $fsm = StateMachine::create([
            $start = 'foo' => [
                'process' => $to = 'bar',
            ],
        ]);
        $this->assertEquals($start, $fsm->getCurrentState());
        $fsm->nextAction();
        $this->assertEquals($to, $fsm->getCurrentState());
        $e = null;
        try {
            $fsm->nextAction();
        } catch (Exception $e) {
        }
        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals(Exception::NO_NEXT_ACTION, $e->getCode());
    }

    public function testForkedNextAction()
    {
        $fsm = StateMachine::create([
            $start = 'foo' => [
                'process1' => $step1 = 'bar',
            ],
            $step1 => [
                'process2' => $step2 = 'hello',
                'process3' => $step3 = 'world',
            ],
        ]);
        $this->assertEquals($start, $fsm->getCurrentState());
        $fsm->nextAction();
        $this->assertEquals($step1, $fsm->getCurrentState());
        $e = null;
        try {
            $fsm->nextAction();
        } catch (Exception $e) {
        }
        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals(Exception::FORKED_NEXT_ACTION, $e->getCode());
    }

    public function testMagicCall()
    {
        $fsm = StateMachine::create([
            $start = 'foo' => [
                'process1' => $step1 = 'bar',
            ],
            $step1 => [
                'process2' => $step2 = 'hello',
                'process3' => $step3 = 'world',
            ],
        ]);
        $this->assertEquals($start, $fsm->getCurrentState());
        $fsm->next();
        $this->assertEquals($step1, $fsm->getCurrentState());
        $fsm->do('process2');
        $this->assertEquals($step2, $fsm->getCurrentState());
    }

    public function testDemonstration()
    {
        $fsm = StateMachine::create([
            'foo' => [
                'process' => 'bar',
            ],
            'bar' => [
                'process2' => 'hello',
                'process3' => 'world',
            ],
        ]);
        ob_start();
        echo $fsm->getCurrentState();   // prints foo
        echo $fsm->next();              // prints bar
        echo $fsm->do('process2');      // prints hello
        $this->assertEquals(implode('', ['foo', 'bar', 'hello']), ob_get_clean());
    }

    public function testHistory()
    {
        $fsm = StateMachine::create([
            $start = 'foo' => [
                'process1' => $step1 = 'bar',
            ],
            $step1 => [
                'process2' => $step2 = 'hello',
            ],
        ]);
        $this->assertEquals($start, $fsm->getCurrentState());
        $fsm->nextAction();
        $this->assertEquals($step1, $fsm->getCurrentState());
        $fsm->nextAction();
        $this->assertEquals($step2, $fsm->getCurrentState());
        $this->assertCount(3, $fsm->getHistory());
        $historicalActions = ['init', 'process1', 'process2'];
        $historicalStates = ['foo', 'bar', 'hello'];
        foreach ($fsm->getHistory() as $k => $v) {
            $this->assertArrayHasKey('time', $v);
            $this->assertArrayHasKey('action', $v);
            $this->assertArrayHasKey('state', $v);
            $this->assertEquals($historicalActions[$k], $v['action']);
            $this->assertEquals($historicalStates[$k], $v['state']);
        }
    }
}
