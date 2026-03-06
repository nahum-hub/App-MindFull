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
     * @return bool
     */
    public function saveResponse($userIdHex, $activityId, $content) {
        $userIdBin = self::uuidToBin($userIdHex);

        // Primero verificamos si la respuesta ya existe
        $stmtCheck = $this->db->prepare("SELECT id FROM user_activity_responses WHERE user_id = ? AND activity_id = ?");
        $stmtCheck->execute([$userIdBin, $activityId]);

        $existing = $stmtCheck->fetch();

        try {
            if ($existing) {
                // Actualiza y asegura que is_completed cambia a 0
                $stmtUpdate = $this->db->prepare("
                    UPDATE user_activity_responses
                    SET response_content = ?, is_completed = 0
                    WHERE id = ?
                ");
                return $stmtUpdate->execute([$content, $existing['id']]);
            } else {
                // Inserta por primera vez marcando is_completed = 0
                $stmtInsert = $this->db->prepare("
                    INSERT INTO user_activity_responses (user_id, activity_id, response_content, is_completed)
                    VALUES (?, ?, ?, 0)
                ");
                return $stmtInsert->execute([$userIdBin, $activityId, $content]);
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
