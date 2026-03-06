<?php

namespace App\Controllers;

class HomeController {

    // Método por defecto que carga la página principal
    public function index() {
        // En una app más robusta, se podrían pasar datos adicionales a la vista
        require '../views/home.php';
    }
}
