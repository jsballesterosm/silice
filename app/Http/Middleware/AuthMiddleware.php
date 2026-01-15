<?php
namespace App\Http\Middleware;

use App\Infrastructure\Repositories\TokenRepository;

class AuthMiddleware
{   
    /**
     * Authenticate the user based on the Authorization header.
     * 
     * @return array|null The authenticated user data or null if authentication fails.
     */
    public static function authenticate(): ?array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!str_starts_with($header, 'Token ')) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Token requerido']);
            exit;
        }

        $token = substr($header, 6);

        $repo = new TokenRepository();
        $user = $repo->findValid($token);

        if (!$user) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }

        return $user;
    }
}
