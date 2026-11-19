<?php

use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../../vendor/autoload.php';

$container = new Container();
$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html');
$dotenv->load();

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addBodyParsingMiddleware();

require_once "routes.php";
require_once "config.php";
require_once "conexion.php";
require_once "eloquent.php";

$app->run();