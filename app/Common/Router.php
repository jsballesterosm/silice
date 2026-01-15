<?php
// app/Common/Router.php
namespace App\Common;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    public function dispatch(): void
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            //$r->addRoute('GET', '/', 'App\Http\Controllers\Web\HomeController@index');
            $r->addRoute('GET', '/api/users', 'App\Http\Controllers\Api\UserController@index');
            $r->addRoute('GET', '/api/users/show', 'App\Http\Controllers\Api\UserController@show');
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = strtok($_SERVER['REQUEST_URI'], '?');

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        if ($routeInfo[0] !== \FastRoute\Dispatcher::FOUND) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        [$class, $method] = explode('@', $routeInfo[1]);
        (new $class())->$method();
    }
}
