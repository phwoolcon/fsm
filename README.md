# Phwoolcon Finite State Machine
[![Build Status](https://travis-ci.org/phwoolcon/fsm.svg?branch=master)](https://travis-ci.org/phwoolcon/fsm)
[![Code Coverage](https://codecov.io/gh/phwoolcon/fsm/branch/master/graph/badge.svg)](https://codecov.io/gh/phwoolcon/fsm)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)

## Usage

```php
<?php
use Phwoolcon\Fsm\StateMachine;
$fsm = StateMachine::create([
    'foo' => [
        'process' => 'bar',
    ],
    'bar' => [
        'process2' => 'hello',
        'process3' => 'world',
    ],
]);
echo $fsm->getCurrentState();   // prints foo
echo $fsm->next();              // prints bar
echo $fsm->do('process2');      // prints hello
```
