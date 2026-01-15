<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Common\Env;
use App\Common\Router;

Env::load();

$router = new Router();
$router->dispatch();
