<?php
// app/Http/Controllers/Web/HomeController.php
namespace App\Http\Controllers\Web;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class HomeController
{
    public function index()
    {
        $viewsPath = dirname(__DIR__, 3) . '/Views'; 
        $loader = new FilesystemLoader($viewsPath);
        $twig = new Environment($loader);

        echo $twig->render('home.twig', [
            'title' => 'Inicio',
            'base_url' => $_ENV['BASE_URL'] 
        ]);
    }
}