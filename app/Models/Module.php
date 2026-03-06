<?php

namespace App\Models;

class Module extends BaseModel {

    /**
     * Obtiene el módulo actual pendiente del usuario o el primero disponible
     *
     * @param string $userIdHex El ID del usuario en hexadecimal
     * @return array|false Datos del módulo o false si no hay.
     */
    public function getCurrentModule($userIdHex) {
        $userIdBin = self::uuidToBin($userIdHex);

        // 1. Verificar si hay un módulo en progreso (is_completed = 1)
        $stmt_prog = $this->db->prepare("
            SELECT mc.*
            FROM user_module_progress ump
            JOIN modules_challenges mc ON ump.module_id = mc.id
            WHERE ump.user_id = ? AND ump.is_completed = 1
            ORDER BY ump.started_at ASC
            LIMIT 1
        ");
        $stmt_prog->execute([$userIdBin]);
        $module = $stmt_prog->fetch();

        if ($module) {
            return $module;
        }

        // 2. Si no hay módulo en progreso, asignar el siguiente basado en el orden numérico
        // que aún no haya sido completado por el usuario.
        $stmt_next = $this->db->prepare("
            SELECT mc.*
            FROM modules_challenges mc
            WHERE mc.active = 1
            AND mc.id NOT IN (
                SELECT module_id FROM user_module_progress WHERE user_id = ? AND is_completed = 0
            )
            ORDER BY mc.numeric_order ASC
            LIMIT 1
        ");
        $stmt_next->execute([$userIdBin]);
        $nextModule = $stmt_next->fetch();

        if ($nextModule) {
            // Inicializar progreso
            $stmt_init = $this->db->prepare("
                INSERT INTO user_module_progress (user_id, module_id, started_at, is_completed)
                VALUES (?, ?, NOW(), 1)
            ");
            $stmt_init->execute([$userIdBin, $nextModule['id']]);
            return $nextModule;
        }

        return false;
    }

    /**
     * Verifica si la palabra clave para desbloquear el módulo es correcta y lo marca completado.
     *
     * @param string $userIdHex
     * @param int $moduleId
     * @param string $keyword
     * @return bool True si es correcta y se actualizó, false en caso contrario
     */
    public function checkKeyword($userIdHex, $moduleId, $keyword) {
        $userIdBin = self::uuidToBin($userIdHex);

        $stmt = $this->db->prepare("SELECT unlock_keyword FROM modules_challenges WHERE id = ?");
        $stmt->execute([$moduleId]);
        $module = $stmt->fetch();

        if ($module) {
            $expectedKeyword = trim($module['unlock_keyword']);

            // Si no requiere palabra clave o si la ingresada coincide
            if (empty($expectedKeyword) || strcasecmp(trim($keyword), $expectedKeyword) === 0) {
                // Actualizamos user_module_progress a is_completed = 0 (Terminado)
                $stmtUpdate = $this->db->prepare("
                    UPDATE user_module_progress
                    SET is_completed = 0, completed_at = NOW()
                    WHERE user_id = ? AND module_id = ?
                ");
                $stmtUpdate->execute([$userIdBin, $moduleId]);
                return true;
            }
        }
        return false;
    }
}
