<?php

namespace App\Models;

class User extends BaseModel {
    /**
     * Registra un nuevo usuario en la base de datos
     * @param string $email
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @return string|false El UUID en formato Hex si fue exitoso, false si falló
     */
    public function register($email, $password, $firstName, $lastName) {
        $uuidHex = bin2hex(random_bytes(16)); // Simplificado para este ejemplo, o usar una función de uuidv4 real
        $uuidBin = self::uuidToBin($uuidHex);
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Obtener el ID del Tier 'Free'
        $stmt_tier = $this->db->prepare("SELECT id FROM subscription_tiers WHERE name = 'Free' LIMIT 1");
        $stmt_tier->execute();
        $tier = $stmt_tier->fetch();
        $tierId = $tier ? $tier['id'] : 1;

        try {
            $this->db->beginTransaction();

            // Insertar usuario
            $stmtUser = $this->db->prepare("INSERT INTO users (id, email, password_hash, tier_id, status) VALUES (?, ?, ?, ?, 'active')");
            $stmtUser->execute([$uuidBin, $email, $passwordHash, $tierId]);

            // Insertar perfil
            $stmtProfile = $this->db->prepare("INSERT INTO user_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmtProfile->execute([$uuidBin, $firstName, $lastName]);

            $this->db->commit();
            return $uuidHex;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Verifica las credenciales del usuario
     * @param string $email
     * @param string $password
     * @return array|false Datos del usuario si es correcto, false si falló
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, password_hash, tier_id FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $user['id_hex'] = self::binToUuid($user['id']);
            unset($user['password_hash']);
            return $user;
        }

        return false;
    }

    /**
     * Obtiene los datos del usuario por su UUID Hexadecimal
     * @param string $uuidHex
     * @return array|false
     */
    public function getById($uuidHex) {
        $uuidBin = self::uuidToBin($uuidHex);

        $stmt = $this->db->prepare("
            SELECT u.id, u.email, u.tier_id, p.first_name, p.last_name, t.name as tier_name
            FROM users u
            JOIN user_profiles p ON u.id = p.user_id
            JOIN subscription_tiers t ON u.tier_id = t.id
            WHERE u.id = ? LIMIT 1
        ");
        $stmt->execute([$uuidBin]);
        $user = $stmt->fetch();

        if ($user) {
            $user['id_hex'] = self::binToUuid($user['id']);
            return $user;
        }

        return false;
    }
}
