<?php
// IF NOT RUNNING PHPUNIT, EXIT DIRECTLY!!
if (! defined('PHPUNIT_RUNNING')) { exit(); }

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

// Require composer autoload!
require_once dirname(__DIR__) .DS .'vendor' .DS .'autoload.php';