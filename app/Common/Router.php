<?php
// app/Common/Router.php
namespace App\Common;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;
use App\Http\Middleware\AuthMiddleware;

class Router
{
    public function dispatch(): void
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {

            // Rutas públicas
            $r->addRoute('GET', '/version', [
                'uses' => 'App\Http\Controllers\Api\SystemController@version',
                'auth' => false
            ]);

            $r->addRoute('GET', '/health', [
                'uses' => 'App\Http\Controllers\Api\SystemController@health',
                'auth' => false
            ]);

            // Rutas protegidas
            $r->addRoute('GET', '/', [
                'uses' => 'App\Http\Controllers\Web\HomeController@index',
                'auth' => false
            ]);

            // vista login
            $r->addRoute('GET', '/auth/login', [
                'uses' => 'App\Http\Controllers\Web\LoginController@index',
                'auth' => false
            ]);

            // login
            $r->addRoute('POST', '/api/auth/login', [
                'uses' => 'App\Http\Controllers\Api\LoginController@login',
                'auth' => false
            ]);

            // usuarios
            $r->addRoute('POST', '/api/users/create', [
                'uses' => 'App\Http\Controllers\Api\UserController@create',
                'auth' => true
            ]);
            $r->addRoute('PUT', '/api/users/update/{id:\d+}', [
                'uses' => 'App\Http\Controllers\Api\UserController@update',
                'auth' => true
            ]);
            $r->addRoute('DELETE', '/api/users/delete/{id:\d+}', [
                'uses' => 'App\Http\Controllers\Api\UserController@delete',
                'auth' => true
            ]);
            $r->addRoute('GET', '/api/users/list', [
                'uses' => 'App\Http\Controllers\Api\UserController@list',
                'auth' => true
            ]);
            $r->addRoute('GET', '/api/users/show/{id:\d+}', [
                'uses' => 'App\Http\Controllers\Api\UserController@show',
                'auth' => true
            ]);
            $r->addRoute('GET', '/api/users/types', [
                'uses' => 'App\Http\Controllers\Api\UserController@types',
                'auth' => true
            ]);
            $r->addRoute('PATCH', '/api/users/password/{id:\d+}', [
                'uses' => 'App\Http\Controllers\Api\UserController@password',
                'auth' => true
            ]);
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'];

        // 🔧 Normalización clásica de URI
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace('/public', '', $uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['error' => 'Not Found']);
                return;

            case Dispatcher::METHOD_NOT_ALLOWED:
                header('Content-Type: application/json');
                http_response_code(405);
                echo json_encode(['error' => 'Method Not Allowed']);
                return;

            case Dispatcher::FOUND:
                break;
        }

        $handler = $routeInfo[1];
        $params  = $routeInfo[2] ?? [];

        // Middleware de autenticación
        if (!empty($handler['auth'])) {
            $GLOBALS['auth_user'] = AuthMiddleware::authenticate();
        }

        [$class, $method] = explode('@', $handler['uses']);
        (new $class())->$method($params);
    }
}
