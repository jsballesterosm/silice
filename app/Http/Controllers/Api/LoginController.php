<?php
// app/Http/Controllers/Api/LoginController.php
namespace App\Http\Controllers\Api;

use App\Infrastructure\Repositories\UserRepository;

class LoginController
{   

    /**
     * Manejamos la petición de login
     * 
     */
    public function login()
    {   
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $user = $body['user'] ?? null;
        $password = $body['password'] ?? null;

        if (!$user) {
            http_response_code(400);
            echo json_encode(['error' => 'Usuario es obligatorio']);
            return;
        }

        
        $repo = new UserRepository();
        $dbUser = $repo->findByUser($user);
        $error_validador = true;
        // el ejercicio dice que el password puede ser vacio
        // si existe el usuario pero la contraseña almacenada es null
        if ($dbUser && $dbUser['password'] === null && $password === '') {
            $error_validador = false;
        }
        // si existe el usuario y la contraseña almacenada no es null y es valida
        elseif ($dbUser && password_verify($password, $dbUser['password'] ?? '')) {
            $error_validador = false;
        }


        if ($error_validador) {
            http_response_code(401);
            echo json_encode(['acceso' => 'Acceso Fallido']);
            return;
        }

        // procedemos a traer los datos del usuario y el token
        http_response_code(200);

        $token = $repo->getTokenByUserId((int)$dbUser['id']);
        $total_datos = $dbUser + ['token' => $token['token_hash'] ?? null] + ['acceso' => 'Acceso Correcto'];

        echo json_encode($total_datos);
    }
}