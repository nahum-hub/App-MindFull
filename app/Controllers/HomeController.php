<?php

namespace App\Controllers;

class HomeController {

    // Método por defecto que carga la página principal
    public function index() {
        // En una app más robusta, se podrían pasar datos adicionales a la vista
        require '../views/home.php';
    }

    // Método para manejar la lógica de redirección del botón "Continuar mi progreso"
    public function continueProgreso() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'module/dashboard');
        } else {
            header('Location: ' . BASE_URL . 'login');
        }
        exit;
    }
}
