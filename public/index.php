<?php

// Autoload our dependencies with Composer
require '../vendor/autoload.php';

// Import dependencies
require '../src/dependencies.php';

// Create a slim app
$app = new \Slim\App;

define('ROOT', realpath('..'));
// Setting the default timezone to Asia/Kolkata
ini_set('date.timezone', 'Asia/Kolkata');

// Routes
require '../src/routes/index.php';

$app->run();

?>