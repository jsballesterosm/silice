<?php
// app/Common/Env.php
namespace App\Common;

use Dotenv\Dotenv;

class Env
{
    public static function load(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
    }
}
