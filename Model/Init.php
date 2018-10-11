<?php
set_time_limit(-1); //
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
define('DB_USER', 'root');
define('DB_PWD', '');
define('DB_NAME','wedding_aggre_db');
define('DB_HOST','localhost');
define('DB_DSN','mysql:host=' . DB_HOST . ';dbname=' . DB_NAME);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
});