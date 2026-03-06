<?php

namespace App\Controllers;

use App\Models\Module;
use App\Models\Activity;

class ChallengeController {

    private $userId;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $this->userId = $_SESSION['user_id'];
    }

    // Ruta: /challenge/view/{id}
    public function view($challengeId = null) {
        if (!$challengeId) {
            header('Location: ' . BASE_URL . 'module/dashboard');
            exit;
        }

        $moduleModel = new Module();
        $activityModel = new Activity();

        $challenge = $moduleModel->getChallengeProgress($this->userId, $challengeId);

        if (!$challenge) {
            header('Location: ' . BASE_URL . 'module/dashboard');
            exit;
        }

        // Calcula el Día Actual
        // Día = (Fecha_Actual - Fecha_Inicio) + 1
        $startDate = new \DateTime($challenge['started_at']);
        // Establecer a las 00:00 para comparar solo días
        $startDate->setTime(0, 0, 0);

        $now = new \DateTime();
        $now->setTime(0, 0, 0);

        $interval = $startDate->diff($now);
        $currentDay = $interval->days + 1;

        // Obtener actividades
        $allActivities = $activityModel->getActivities($this->userId, $challengeId);

        // Filtrar solo la del día actual
        $todayActivity = null;
        $allCompleted = true;

        foreach ($allActivities as $act) {
            if ($act['is_completed'] !== 0) {
                $allCompleted = false;
            }
            if ((int)$act['position'] === $currentDay) {
                $todayActivity = $act;
            }
        }

        // Si el reto está completado, is_completed será 0
        $isFinished = ($challenge['is_completed'] == 0);

        require '../views/module/challenge.php';
    }
}