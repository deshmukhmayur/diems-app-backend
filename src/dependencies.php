<?php

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
require '../config/settings.php';

// Bootstrap Eloquent ORM
$container = new Illuminate\Container\Container;
$connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
$conn = $connFactory->make($settings);
$resolver = new \Illuminate\Database\ConnectionResolver();
$resolver->addConnection('default', $conn);
$resolver->setDefaultConnection('default');
\Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);

?>