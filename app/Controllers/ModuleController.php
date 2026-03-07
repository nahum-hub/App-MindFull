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

        // Obtener nuevos datos: retos activos e historial de completados
        // Se cargan antes del posible early return para asegurar que siempre estén disponibles en la vista
        $activeChallenges = $moduleModel->getUserActiveChallenges($this->userId);
        $completedHistory = $moduleModel->getCompletedHistory($this->userId);

        // Obtener el módulo actual (el pendiente, o inicializar el siguiente)
        $currentModule = $moduleModel->getCurrentModule($this->userId);

        if (!$currentModule) {
            // ESTADO C (Finalizado global): No hay módulo actual ni siguiente disponible
            $msg = "¡Felicidades! Has completado todos los módulos actuales.";
            require '../views/module/dashboard.php';
            return;
        }

        // Obtener actividades
        $activities = $activityModel->getActivities($this->userId, $currentModule['id']);

        // ESTADO A y B: Comprobar si todas las actividades están completadas basándose en el conteo de DB
        $allCompleted = $activityModel->isModuleCompleted($this->userId, $currentModule['id']);

        // ESTADO B (Pendiente de Palabra): Todas las actividades en 0, pero user_module_progress sigue en 1
        // Si no se cumple, estamos en ESTADO A (Activo)
        $showUnlockCard = $allCompleted;

        require '../views/module/dashboard.php';
    }

    // Ruta: /mi-progreso
    public function miProgreso() {
        $moduleModel = new Module();
        $completedHistory = $moduleModel->getCompletedHistory($this->userId);

        $modulosTerminados = [];
        $retosTerminados = [];

        foreach ($completedHistory as $item) {
            // Asumiendo que los retos tienen tipo 'reto_semanal', 'reto_mensual', 'reto_individual'
            if (strpos($item['type'], 'reto') !== false) {
                $retosTerminados[] = $item;
            } else {
                $modulosTerminados[] = $item;
            }
        }

        require '../views/module/progreso.php';
    }

    // Ruta: /module/saveActivity (se llama por AJAX o POST)
    public function saveActivity() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $activityId = (int)($_POST['activity_id'] ?? 0);
            $content = trim($_POST['response'] ?? '');

            if ($activityId > 0 && !empty($content)) {
                $activityModel = new Activity();
                $result = $activityModel->saveResponse($this->userId, $activityId, $content);

                // Si es una petición AJAX, devolver JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode($result);
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
