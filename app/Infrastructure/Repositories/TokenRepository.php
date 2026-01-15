<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Database\Connection;
use DateTime;
use PDO;

class TokenRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::make();
    }

    /**
     * Crea un nuevo token para un usuario.
     * 
     * @param int $userId ID del usuario.
     * @param string $token Token generado.
     * @param int $ttlSeconds Tiempo de vida del token en segundos.
     * @return void
     */
    public function create(int $userId, string $token, int $ttlSeconds): void
    {
        $expires = (new DateTime())
            ->modify("+{$ttlSeconds} seconds")
            ->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO usuarios_tokens (user_id, token_hash, expires_at)
            VALUES (:user_id, :token_hash, :expires_at)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':token_hash' => hash('sha256', $token),
            ':expires_at' => $expires
        ]);
    }

    /**
     * Busca un token válido y devuelve el usuario asociado.
     * 
     * @param string $token Token a buscar.
     * @return array|null Datos del usuario si el token es válido, null en caso contrario
     */
    public function findValid(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*
            FROM usuarios_tokens t
            JOIN usuarios u ON u.id = t.user_id
            WHERE t.token_hash = :hash
            LIMIT 1
        ");

        $stmt->execute([
            ':hash' => $token
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Busca un usuario por su token.
     * 
     * @param string $token Token a buscar.
     * @return array|null Datos del usuario si se encuentra, null en caso contrario.
     */
    public function getUserByToken(string $token): array|null
    {
        $stmt = $this->db->prepare(
            'SELECT u.* FROM usuarios u
            JOIN usuarios_tokens t ON u.id = t.user_id
            WHERE t.token_hash = :token_hash
            LIMIT 1'
        );

        $stmt->execute([':token_hash' => hash('sha256', $token)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }
}
