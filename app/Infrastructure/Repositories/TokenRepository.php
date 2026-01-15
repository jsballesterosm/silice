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
}
