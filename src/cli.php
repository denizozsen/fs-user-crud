<?php

// Composer class auto loader
include __DIR__ . '/../vendor/autoload.php';

// TODO - the database credentials and ORM registration should be done via some kind of configuration mechanism

// Connect to db, using PDO
$host = '127.0.0.1';
$db = 'my_app';
$user = 'my_app';
$password = 'secret';
$dsn = "mysql:dbname={$db};host={$host}";
$pdo = new PDO($dsn, $user, $password);

// Tell PDO to throw exceptions on errors
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);;

// Tell DenOrm to use the PDO instance we've just created
\AOrm\Registry::registerPdoConnection($pdo);

// Instantiate the application
$application = new \FsTest\User\UserCliApp();

// Start the application
$application->start();
