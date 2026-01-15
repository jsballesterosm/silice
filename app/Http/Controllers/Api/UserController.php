<?php
// app/Http/Controllers/Api/UserController.php
namespace App\Http\Controllers\Api;

use App\Common\Validation\UserValidator;
use App\Infrastructure\Repositories\UserRepository;
use App\Common\TokenService;
use App\Infrastructure\Repositories\TokenRepository;

class UserController
{
    /**
     * Handle user creation request
     * 
     * @return void
     */
    public function create(): void {
        header('Content-Type: application/json');

        $body = json_decode(file_get_contents('php://input'), true);
        $errors = UserValidator::validateCreate($body);

        // procedemos a pintar los errores si los hay
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'errors' => $errors
            ]);
            return;
        }

        // ahora crearíamos el usuario en la base de datos.

        // ciframos la contraseña
        $passwordHash = null;
        if (!empty($body['password'])) {
            $passwordHash = password_hash($body['password'], PASSWORD_BCRYPT);
        }
        
        // ahora procedemos a registrar al usuario en la base de datos
        $repo = new UserRepository();
        $userId = $repo->create([
            'user' => $body['user'],
            'password' => $passwordHash,
            'nombre_apellidos' => $body['nombreApellidos'],
            'correo' => $body['correo'],
            'nif' => $body['nif'] ?? null,
            'tipo_id' => (int)$body['tipo_id']
        ]);

        // ahora procedemos a asigarnel el token de autenticación
        $token = TokenService::generate();

        $tokenRepo = new TokenRepository();
        $tokenRepo->create(
            $userId,
            $token,
            3600 // 1 hora
        );

        http_response_code(201);
        echo json_encode([
            'message' => 'Usuario creado',
            'id' => $userId,
            'token' => $token
        ]);
    }

    /**
     * Handle user update request
     * 
     * @return void
     */
    public function update(array $args): void
    {
        header('Content-Type: application/json');

        $id = (int) ($args['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            UserValidator::validateUpdate($body, $id);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }

        $repo = new UserRepository();

        if (!$repo->exists($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no existe']);
            return;
        }

        $repo->update($id, $body);

        echo json_encode(['message' => 'Usuario actualizado']);
    }

    /**
     * Handle user deletion request
     * 
     * @return void
     * 
     */
    public function delete(array $args): void
    {
        header('Content-Type: application/json');

        $id = (int) ($args['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        $repo = new UserRepository();

        if (!$repo->exists($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no existe']);
            return;
        }

        $repo->deleteUserById($id);

        echo json_encode(['message' => 'Usuario eliminado']);
    }
}