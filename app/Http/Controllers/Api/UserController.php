<?php
// app/Http/Controllers/Api/UserController.php
namespace App\Http\Controllers\Api;

class UserController
{
    public function index()
    {
        header('Content-Type: application/json');
        echo json_encode([
            ['id' => 1, 'name' => 'Juan'],
            ['id' => 2, 'name' => 'Ana']
        ]);
    }

    public function show()
    {
        header('Content-Type: application/json');
        echo json_encode(['app_env' => $_ENV['APP_ENV'] ?? 'development']);
    }
}