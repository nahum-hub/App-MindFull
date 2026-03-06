<?php

namespace App\Controllers;

use App\Models\User;

class AuthController {

    // Recibe GET (muestra vista) y POST (procesa registro)
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
                header('Location: ' . BASE_URL . 'login?msg=registered');
                exit;
            } else {
                $error = "El correo electrónico ya está registrado o hubo un error al crear la cuenta.";
                require '../views/auth/register.php';
            }
        } else {
            // GET request
            require '../views/auth/register.php';
        }
    }

    // Recibe GET (muestra vista) y POST (procesa login)
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

                // Redirigir al dashboard
                header('Location: ' . BASE_URL . 'module/dashboard');
                exit;
            } else {
                $error = "Credenciales incorrectas.";
                require '../views/auth/login.php';
            }
        } else {
            // GET request
            require '../views/auth/login.php';
        }
    }

    // Destruye la sesión y manda al inicio
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '');
        exit;
    }
}
