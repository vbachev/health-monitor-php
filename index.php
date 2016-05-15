<?php

// error_reporting(E_ALL);
// ini_set('memory_limit', '256M');

// sensitive configuration is stored in a separate file
$settings['environment'] = parse_ini_file( 'config.ini', true );

// database management
require_once 'includes/db.class.php';
$db = new Db(
    $settings['environment']['database']['host'], 
    $settings['environment']['database']['user'], 
    $settings['environment']['database']['password'], 
    $settings['environment']['database']['database'] 
);

// business logic
require_once 'includes/functions.php';

// request routing
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    handlePostRequest();
} else if($_SERVER['REQUEST_METHOD'] === 'GET'){
    handleGetRequest();
}