<?php

namespace App\Controllers;

use App\Models\User;

class AuthController {

    // Muestra la vista de Registro
    public function showRegister() {
        require '../views/auth/register.php';
    }

    // Muestra la vista de Login
    public function showLogin() {
        require '../views/auth/login.php';
    }

    // Recibe POST para registrar
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                $error = "Todos los campos son obligatorios.";
                require '../views/auth/register.php';
                return;
            }

            $userModel = new User();

            // Validar si el email ya existe se hace dentro del método create()
            $success = $userModel->create($email, $password, $firstName, $lastName);

            if ($success) {
                // Redirige al login tras el éxito
                header('Location: /auth/showLogin?msg=registered');
                exit;
            } else {
                $error = "El correo electrónico ya está registrado o hubo un error al crear la cuenta.";
                require '../views/auth/register.php';
            }
        } else {
            // Si llegan por GET a /auth/register en lugar de showRegister, mostramos la vista
            $this->showRegister();
        }
    }

    // Recibe POST para loguearse
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Todos los campos son obligatorios.";
                require '../views/auth/login.php';
                return;
            }

            $userModel = new User();
            $user = $userModel->getByEmail($email);

            // Valida el email y verifica la contraseña con password_verify()
            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['status'] !== 'active') {
                    $error = "Tu cuenta se encuentra inactiva.";
                    require '../views/auth/login.php';
                    return;
                }

                // Inicia la sesión guardando el user_id (hexadecimal) y el tier_id
                $_SESSION['user_id'] = $user['id_hex'];
                $_SESSION['tier_id'] = $user['tier_id'];

                // Redirigir al dashboard (lógica de ModuleController a implementar)
                header('Location: /module/dashboard');
                exit;
            } else {
                $error = "Credenciales incorrectas.";
                require '../views/auth/login.php';
            }
        } else {
            // Si llegan por GET a /auth/login en lugar de showLogin, mostramos la vista
            $this->showLogin();
        }
    }

    // Destruye la sesión y manda al login
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: /auth/showLogin');
        exit;
    }
}
