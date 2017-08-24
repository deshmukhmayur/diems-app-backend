<?php

// Autoload our dependencies with Composer
require '../vendor/autoload.php';

// Import dependencies
require '../src/dependencies.php';

// Create a slim app
$app = new \Slim\App;

// Routes
require '../src/routes/index.php';

$app->run();

?>