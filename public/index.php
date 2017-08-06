<?php

// Autoload our dependencies with Composer
require '../vendor/autoload.php';

// Import the database information from /src/settings.php
// e.g.
// $settings = array(
//     'driver' => 'mysql',
//     'host' => 'localhost',
//     'database' => 'database',
//     'username' => 'username',
//     'password' => 'password',
//     'collation' => 'utf8_general_ci',
//     'prefix' => '',
// );
require '../src/settings.php';

// Import dependencies
require '../src/dependencies.php';

// Create a slim app
$app = new \Slim\App;

// Routes
require '../src/routes.php';

$app->run();

?>