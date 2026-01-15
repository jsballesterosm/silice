<?php

namespace App\Infrastructure\Repositories;

use App\Infrastructure\Database\Connection;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::make();
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * 
     * @param array $user Datos del usuario a crear.
     * @return int ID del usuario creado.
     */
    public function create(array $user): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios
            (user, password, nombre_apellidos, correo, nif, tipo_id)
            VALUES
            (:user, :password, :nombre, :correo, :nif, :tipo_id)
        ");

        $stmt->execute([
            ':user' => $user['user'],
            ':password' => $user['password'],
            ':nombre' => $user['nombre_apellidos'],
            ':correo' => $user['correo'],
            ':nif' => $user['nif'],
            ':tipo_id' => $user['tipo_id']
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Verifica si un usuario con el nombre dado ya existe.
     * 
     * @param string $user Nombre de usuario a verificar.
     * @return bool Verdadero si el usuario existe, falso en caso contrario.
     */
    public function existsByUser(string $user): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE user = :user LIMIT 1'
        );

        $stmt->execute([':user' => $user]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Verifica si un usuario con el correo dado ya existe.
     * 
     * @param string $correo Correo electrónico a verificar.
     * @return bool Verdadero si el correo existe, falso en caso contrario.
     */
    public function existsByEmail(string $correo): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE correo = :correo LIMIT 1'
        );

        $stmt->execute([':correo' => $correo]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Verifica si un usuario con el correo dado ya existe, excluyendo un ID específico.
     * 
     * @param string $correo Correo electrónico a verificar.
     * @param int $id ID del usuario a excluir de la verificación.
     * @return bool Verdadero si el correo existe, falso en caso contrario.
     */
    public function existsEmailByDiffId(string $correo, int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE correo = :correo AND id != :id LIMIT 1'
        );

        $stmt->execute([':correo' => $correo, ':id' => $id]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Verifica si un usuario con el NIF dado ya existe, excluyendo un ID específico.
     * 
     * @param string $nif NIF a verificar.
     * @param int $id ID del usuario a excluir de la verificación.
     * @return bool Verdadero si el NIF existe, falso en caso contrario.
     */
    public function existsNifByDiffId(string $nif, int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE nif = :nif AND id != :id LIMIT 1'
        );

        $stmt->execute([':nif' => $nif, ':id' => $id]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Verifica si un tipo de usuario con el ID dado existe.
     * 
     * @param int $tipo_id ID del tipo de usuario a verificar.
     * @return bool Verdadero si el tipo de usuario existe, falso en caso contrario.
     */
    public function existsTipoId(int $tipo_id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM tipos WHERE id = :tipo_id LIMIT 1'
        );

        $stmt->execute([':tipo_id' => $tipo_id]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Verifica si un usuario con el NIF dado ya existe.
     * 
     * @param string $nif NIF a verificar.
     * @return bool Verdadero si el NIF existe, falso en caso contrario.
     */
    public function existNif(string $nif): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE nif = :nif LIMIT 1'
        );

        $stmt->execute([':nif' => $nif]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Busca un usuario por su nombre de usuario.
     * 
     * @param string $user Nombre de usuario a buscar.
     * @return array|null Datos del usuario si se encuentra, null en caso contrario.
     */
    public function findByUser(string $user): array|null
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios WHERE user = :user LIMIT 1'
        );

        $stmt->execute([':user' => $user]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * Busca un token por el ID de usuario.
     * 
     * @param int $userId ID del usuario.
     * @return array|null Datos del token si se encuentra, null en caso contrario.
     */
    public function getTokenByUserId(int $userId): array|null
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios_tokens WHERE user_id = :user_id LIMIT 1'
        );

        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * 
     * Elimina los tokens de un usuario por su ID.
     * 
     * @param int $userId ID del usuario cuyos tokens se eliminarán.
     */
    public function deleteTokenByUserId(int $userId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM usuarios_tokens WHERE user_id = :user_id'
        );

        $stmt->execute([':user_id' => $userId]);
    }


    /**
     * Elimina un usuario por su ID.
     * 
     * @param int $userId ID del usuario a eliminar.
     * @return void
     */
    public function deleteUserById(int $userId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM usuarios WHERE id = :user_id'
        );

        $stmt->execute([':user_id' => $userId]);
    }

    /**
     * 
     * Busca un usuario por su ID.
     * 
     * @param int $id ID del usuario a buscar.
     * @return array|null Datos del usuario si se encuentra, null en caso contrario.
     */
    public function findById(int $id): array|null
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios WHERE id = :id LIMIT 1'
        );

        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * Verifica si un usuario con el ID dado existe.
     * 
     * @param int $id ID del usuario a verificar.
     * @return bool Verdadero si el usuario existe, falso en caso contrario.
     */
    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM usuarios WHERE id = :id LIMIT 1'
        );

        $stmt->execute([':id' => $id]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Actualiza los datos de un usuario.
     * 
     * @param int $id ID del usuario a actualizar.
     * @param array $data Datos a actualizar.
     * @return void
     */
    public function update(int $id, array $data): void
    {
        $fields = [];
        $params = [':id' => $id];

        // Construir la consulta dinámicamente según los campos proporcionados
        if (isset($data['nombreApellidos'])) {
            $fields[] = 'nombre_apellidos = :nombreApellidos'; 
            $params['nombreApellidos'] = $data['nombreApellidos'];
        }

        if (isset($data['correo'])) {
            $fields[] = 'correo = :correo'; 
            $params['correo'] = $data['correo'];
        }

        if (isset($data['nif'])) { 
            $fields[] = 'nif = :nif'; 
            $params['nif'] = $data['nif']; 
        }

        if (isset($data['tipo_id'])) { 
            $fields[] = 'tipo_id = :tipo_id'; 
            $params['tipo_id'] = $data['tipo_id']; 
        }

        if (!$fields) { 
            return; 
        }

        $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }
}