<?php
namespace App\Common;

use App\Infrastructure\Repositories\TokenRepository;

class TokenService
{
    public static function generate(): string
    {
        return bin2hex(random_bytes(32)); // 64 chars
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function getUserIdFromToken(string $token): int|null
    {
        // Aquí podríamos implementar la lógica para extraer el ID de usuario
        // desde el token si fuera necesario. Por ahora, devolvemos null.
        $repo = new TokenRepository();

        $rs = $repo->findValid($token);
        if ($rs) {
            return (int)$rs['id'];
        }
        return null;
    }
}
