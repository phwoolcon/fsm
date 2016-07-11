<?php

error_reporting(-1);
$_SERVER['PHWOOLCON_ENV'] = 'testing';

define('TEST_ROOT_PATH', __DIR__ . '/root');

$_SERVER['PHWOOLCON_ROOT_PATH'] = TEST_ROOT_PATH;
$_SERVER['PHWOOLCON_CONFIG_PATH'] = TEST_ROOT_PATH . '/app/config';

// Register class loader
include __DIR__ . '/../vendor/autoload.php';
