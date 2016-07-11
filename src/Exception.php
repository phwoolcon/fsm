<?php
namespace Phwoolcon\Fsm;

use LogicException;

class Exception extends LogicException
{
    const INVALID_ACTION = 10;
    const NO_NEXT_ACTION = 20;
    const FORKED_NEXT_ACTION = 30;
}
