<?php
namespace App\Common;

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
}
