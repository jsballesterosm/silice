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
     * Manejamos la petición de actualización de un usuario
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

        $errors = UserValidator::validateUpdate($body, $id);

        // procedemos a pintar los errores si los hay
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'errors' => $errors
            ]);
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
     * Manejamos la petición de eliminación de un usuario
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

        // aqui hacemos una validacion para que se pueda eliminar el usuario que esta realizando
        // la peticion.
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Token ', '', $token);

        $actualUserId = TokenService::getUserIdFromToken($token);

        if ($actualUserId == $id) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado para eliminar este usuario']);
            return;
        }

        // si todo anda bien procedemos a eliminar el usuario
        // primero eliminamos los tokens asociados al usuario
        $repo->deleteTokenByUserId($id);
        $repo->deleteUserById($id);

        echo json_encode(['message' => 'Usuario eliminado']);
    }

    public function list(): void
    {
        header('Content-Type: application/json');

        $repo = new UserRepository();
        $users = $repo->getAllUsers();

        echo json_encode($users);
    }

    public function show(array $args): void
    {
        header('Content-Type: application/json');

        $id = (int) ($args['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        $repo = new UserRepository();
        $user = $repo->findById($id);

        if ($user === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        echo json_encode($user);
    }

    /**
     * Maneja la petición para obtener los tipos de usuario.
     * 
     * @return void
     */
    public function types(): void
    {
        header('Content-Type: application/json');

        $repo = new UserRepository();
        $types = $repo->getUserTypes();

        echo json_encode($types);
    }

    public function password(array $args): void
    {
        header('Content-Type: application/json');

        $id = (int) ($args['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $user = $body['user'] ?? null;
        $password = $body['admin_password'] ?? '';
        $new_password = $body['new_password'] ?? '';

        $errors = UserValidator::validatePasswordChange($body);

        // procedemos a pintar los errores si los hay
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'errors' => $errors
            ]);
            return;
        }

        $repo = new UserRepository();

        if (!$repo->exists($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no existe']);
            return;
        }

        // validamos las credenciales del administrador
        $dbUser = $repo->findByUser($user);
        $error_validador = false;
        // el ejercicio dice que el password puede ser vacio
        if(!$dbUser || !password_verify($password, $dbUser['password'] ?? '')) {
            $error_validador = true;
        }

        if ($error_validador) {
            http_response_code(401);
            echo json_encode(['errors' => ['admin_password' => 'Credenciales de administrador inválidas']]);
            return;
        }

        $passwordHash = null;
        if (!empty($new_password)) {
            $passwordHash = password_hash($new_password, PASSWORD_BCRYPT);
        }

        $repo->updatePassword($id, $passwordHash);

        echo json_encode(['message' => 'Contraseña actualizada']);
    }
}