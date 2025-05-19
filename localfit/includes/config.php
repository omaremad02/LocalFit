<?php
// Turn on error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session start
session_start();

// Define constants
define('ROOT_PATH', dirname(__DIR__) . '/');
define('SITE_URL', 'http://localhost/localfit/');
define('SITE_NAME', 'LocalFit');
?>