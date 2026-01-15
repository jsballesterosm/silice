<?php
// app/Http/Controllers/Api/SystemController.php
namespace App\Http\Controllers\Api;

use App\Infrastructure\Database\Connection;
use PDOException;

use App\Infrastructure\Repositories\UserRepository;
use App\Common\TokenService;
use App\Infrastructure\Repositories\TokenRepository;

class SystemController
{
    /**
     * Devuelve información de versión
     */
    public function version(): void
    {
        header('Content-Type: application/json');

        echo json_encode([
            'name' => 'Silice',
            'version' => '1.0.0',
            'php' => PHP_VERSION,
            'environment' => $_ENV['APP_ENV'] ?? 'unknown'
        ]);
    }

    /**
     * Health check
     */
    public function health(): void
    {
        header('Content-Type: application/json');

        try {
            $pdo = Connection::make();
            $pdo->query('SELECT 1');

            echo json_encode([
                'status' => 'UP',
                'database' => 'OK',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                'status' => 'DOWN',
                'database' => 'ERROR'
            ]);
        }
    }
}
