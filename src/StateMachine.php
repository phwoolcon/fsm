<?php
namespace Phwoolcon\Fsm;

use Closure;

/**
 * Class StateMachine
 * @package Phwoolcon\Fsm
 *
 * @method string do(string $action, mixed &$payload = null)
 * @method string init(string $action)
 * @method string next(string $action)
 */
class StateMachine
{
    protected static $instances = [];
    protected $initState;
    protected $transitions = [];
    protected $currentState;
    protected $previousState;
    protected $history = [];

    /**
     * StateMachine constructor.
     * @param array $transitions
     *
     * $transitions example:
     *  [
     *      'State1' => [
     *          'action1' => 'State1',
     *          'action2' => 'State1',
     *      ],
     *      'State2' => [
     *          'action3' => 'State3',
     *      ],
     *      'State3' => [
     *          'action4' => 'State1',
     *      ],
     *  ]
     *
     * You may use Closure to do real actions in the target state:
     *  [
     *      'State1' => [
     *          'action1' => 'State1',
     *          'action2' => 'State1',
     *      ],
     *      'State2' => [
     *          'action3' => function ($stateMachine) {
     *              // Do something here
     *              return 'State3';
     *          },
     *      ],
     *      'State3' => [
     *          'action4' => 'State1',
     *      ],
     *  ]
     */
    public function __construct(array $transitions)
    {
        $this->transitions = $transitions;
        $this->initAction();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this, $name . 'Action'], $arguments);
    }

    public function addTransition($fromState, $action, $toState)
    {
        $this->transitions[$fromState][$action] = $toState;
    }

    public function doAction($action, &$payload = null)
    {
        if (!isset($this->transitions[$this->currentState][$action])) {
            throw new Exception(sprintf('Invalid action "%s" for current state "%s"', $action, $this->currentState));
        }
        if (($state = $this->transitions[$this->currentState][$action]) instanceof Closure) {
            $state = $state($this, $payload);
        }
        $this->previousState = $this->currentState;
        $this->currentState = $state;
        $this->history[] = ['action' => $action, 'state' => $state];
        return $state;
    }

    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * @param string $name
     * @param array  $transitions
     * @return static
     */
    public static function getInstance($name, $transitions = [])
    {
        isset(static::$instances[$name]) or static::$instances[$name] = new static($transitions);
        return static::$instances[$name];
    }

    public function initAction()
    {
        foreach ($this->transitions as $state => $transition) {
            $this->history[] = ['action' => 'init', 'state' => $state];
            return $this->initState = $this->currentState = $state;
        }
        return $this->currentState;
    }

    public function nextAction()
    {
        if (!isset($this->transitions[$this->currentState])) {
            throw new Exception(sprintf('No further actions for current state "%s"', $this->currentState));
        }
        if (count($transition = $this->transitions[$this->currentState]) != 1) {
            throw new Exception('Unable to call next on a forked state');
        }
        foreach ($transition as $action => $toState) {
            return $this->doAction($action);
        }
        return $this->currentState;
    }

    public function reset()
    {
        $this->currentState = $this->initState;
        $this->previousState = null;
        $this->history = [];
    }
}
