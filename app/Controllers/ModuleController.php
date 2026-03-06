<?php

namespace App\Controllers;

use App\Models\Module;
use App\Models\Activity;

class ModuleController {

    private $userId;

    public function __construct() {
        // Redirección: Si no está logueado e intenta entrar, a /login
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $this->userId = $_SESSION['user_id'];
    }

    // Ruta: /module/dashboard
    public function dashboard() {
        $moduleModel = new Module();
        $activityModel = new Activity();

        // Obtener el módulo actual (el pendiente, o inicializar el siguiente)
        $currentModule = $moduleModel->getCurrentModule($this->userId);

        if (!$currentModule) {
            // Ya no hay módulos disponibles
            $msg = "¡Felicidades! Has completado todos los módulos actuales.";
            require '../views/module/dashboard.php';
            return;
        }

        // Obtener actividades
        $activities = $activityModel->getActivities($this->userId, $currentModule['id']);

        // Comprobar si todas las actividades están en 0 (Completado)
        $allCompleted = true;
        foreach ($activities as $act) {
            // is_completed: 1 = Pendiente, 0 = Completado
            if ($act['is_completed'] !== 0) {
                $allCompleted = false;
                break;
            }
        }

        require '../views/module/dashboard.php';
    }

    // Ruta: /module/saveActivity (se llama por AJAX o POST)
    public function saveActivity() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $activityId = (int)($_POST['activity_id'] ?? 0);
            $content = trim($_POST['response'] ?? '');

            if ($activityId > 0 && !empty($content)) {
                $activityModel = new Activity();
                $success = $activityModel->saveResponse($this->userId, $activityId, $content);

                // Si es una petición AJAX, devolver JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['success' => $success]);
                    exit;
                }
            }

            // Si es post tradicional, volver al dashboard
            header('Location: ' . BASE_URL . 'module/dashboard');
            exit;
        }
    }

    // Ruta: /module/unlock
    public function unlock() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $moduleId = (int)($_POST['module_id'] ?? 0);
            $keyword = trim($_POST['keyword'] ?? '');

            if ($moduleId > 0) {
                $moduleModel = new Module();
                $success = $moduleModel->checkKeyword($this->userId, $moduleId, $keyword);

                if ($success) {
                    header('Location: ' . BASE_URL . 'module/dashboard?msg=unlocked');
                    exit;
                } else {
                    header('Location: ' . BASE_URL . 'module/dashboard?error=wrong_keyword');
                    exit;
                }
            }
        }
        header('Location: ' . BASE_URL . 'module/dashboard');
        exit;
    }
}
