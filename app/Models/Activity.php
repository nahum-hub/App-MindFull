<?php

namespace App\Models;

class Activity extends BaseModel {

    /**
     * Obtiene las actividades para un módulo específico y las respuestas del usuario si las hay.
     *
     * @param string $userIdHex El ID del usuario
     * @param int $moduleId El ID del módulo
     * @return array La lista de actividades
     */
    public function getActivities($userIdHex, $moduleId) {
        $userIdBin = self::uuidToBin($userIdHex);

        $stmt = $this->db->prepare("
            SELECT a.id, a.content_text, a.position, uar.response_content, uar.is_completed
            FROM activities a
            LEFT JOIN user_activity_responses uar ON a.id = uar.activity_id AND uar.user_id = ?
            WHERE a.module_id = ?
            ORDER BY a.position ASC
        ");
        $stmt->execute([$userIdBin, $moduleId]);
        return $stmt->fetchAll();
    }

    /**
     * Guarda la respuesta de una actividad y actualiza is_completed a 0 (Completado).
     *
     * @param string $userIdHex
     * @param int $activityId
     * @param string $content
     * @return array
     */
    public function saveResponse($userIdHex, $activityId, $content) {
        $userIdClean = str_replace('-', '', $userIdHex);

        try {
            // Primero verificamos si la respuesta ya existe
            $stmtCheck = $this->db->prepare("SELECT id FROM user_activity_responses WHERE user_id = UNHEX(?) AND activity_id = ?");
            $stmtCheck->execute([$userIdClean, $activityId]);
            $existing = $stmtCheck->fetch();

            if ($existing) {
                // Actualiza y asegura que is_completed cambia a 0
                $stmtUpdate = $this->db->prepare("
                    UPDATE user_activity_responses
                    SET response_content = ?, is_completed = 0
                    WHERE user_id = UNHEX(?) AND activity_id = ?
                ");
                $success = $stmtUpdate->execute([$content, $userIdClean, $activityId]);
            } else {
                // Inserta por primera vez marcando is_completed = 0
                $stmtInsert = $this->db->prepare("
                    INSERT INTO user_activity_responses (user_id, activity_id, response_content, is_completed)
                    VALUES (UNHEX(?), ?, ?, 0)
                ");
                $success = $stmtInsert->execute([$userIdClean, $activityId, $content]);
            }

            return ['success' => $success];

        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verifica si todas las actividades de un módulo han sido completadas por el usuario.
     *
     * @param string $userIdHex
     * @param int $moduleId
     * @return bool
     */
    public function isModuleCompleted($userIdHex, $moduleId) {
        $userIdClean = str_replace('-', '', $userIdHex);

        try {
            // Contar total de actividades del módulo
            $stmtTotal = $this->db->prepare("SELECT COUNT(*) FROM activities WHERE module_id = ?");
            $stmtTotal->execute([$moduleId]);
            $totalActivities = (int) $stmtTotal->fetchColumn();

            // Si el módulo no tiene actividades, ¿se considera completado? Por regla general, no o dependemos del keyword.
            if ($totalActivities === 0) {
                return false;
            }

            // Contar actividades completadas por el usuario para este módulo (is_completed = 0)
            $stmtCompleted = $this->db->prepare("
                SELECT COUNT(*)
                FROM user_activity_responses
                WHERE user_id = UNHEX(?)
                AND is_completed = 0
                AND activity_id IN (SELECT id FROM activities WHERE module_id = ?)
            ");
            $stmtCompleted->execute([$userIdClean, $moduleId]);
            $completedActivities = (int) $stmtCompleted->fetchColumn();

            return ($completedActivities === $totalActivities);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
