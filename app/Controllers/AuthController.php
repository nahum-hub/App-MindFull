<?php

namespace App\Controllers;

use App\Models\User;

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Todos los campos son obligatorios.";
                require '../views/login.php';
                return;
            }

            $userModel = new User();
            $user = $userModel->login($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id_hex'];
                header('Location: /module/dashboard'); // Redirige al Dashboard de módulos
                exit;
            } else {
                $error = "Credenciales incorrectas.";
                require '../views/login.php';
            }
        } else {
            require '../views/login.php';
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                $error = "Todos los campos son obligatorios.";
                require '../views/register.php';
                return;
            }

            $userModel = new User();
            $uuidHex = $userModel->register($email, $password, $firstName, $lastName);

            if ($uuidHex) {
                $_SESSION['user_id'] = $uuidHex;
                header('Location: /module/dashboard'); // Redirige al Dashboard de módulos
                exit;
            } else {
                $error = "Error al registrar el usuario. El email podría estar en uso.";
                require '../views/register.php';
            }
        } else {
            require '../views/register.php';
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: /auth/login');
        exit;
    }
}
