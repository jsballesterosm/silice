<?php
// app/Http/Controllers/Web/LoginController.php
namespace App\Http\Controllers\Web;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class LoginController
{
    public function index()
    {
        $viewsPath = dirname(__DIR__, 3) . '/Views'; 
        $loader = new FilesystemLoader($viewsPath);
        $twig = new Environment($loader);

        echo $twig->render('login.twig', [
            'title' => 'Inicio'
        ]);
    }
}