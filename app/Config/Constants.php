<?php

//----------------------------------------------------------------------
// App Namespace
//----------------------------------------------------------------------
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

//----------------------------------------------------------------------
// Timing & Debug
//----------------------------------------------------------------------
defined('APP_START_TIME') || define('APP_START_TIME', microtime(true));
defined('CI_DEBUG') || define('CI_DEBUG', env('CI_ENVIRONMENT') !== 'production');
