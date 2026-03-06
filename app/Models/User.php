<?php

namespace App\Models;

class User extends BaseModel {

    /**
     * Inserta un nuevo usuario (y su perfil) usando MySQL UUID() convertido a binario.
     * Tier por defecto: 1 (Free).
     *
     * @param string $email
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @return bool True si se creó con éxito, false si el email ya existe o hubo error.
     */
    public function create($email, $password, $firstName, $lastName) {
        // Verificar si el email ya existe
        if ($this->getByEmail($email)) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $this->db->beginTransaction();

            // Insertar usuario: Generamos el UUID en MySQL, eliminamos los guiones y lo convertimos a UNHEX (BINARY 16)
            $stmtUser = $this->db->prepare("
                INSERT INTO users (id, email, password_hash, tier_id, status)
                VALUES (UNHEX(REPLACE(UUID(), '-', '')), ?, ?, 1, 'active')
            ");
            $stmtUser->execute([$email, $passwordHash]);

            // Obtener el ID insertado para guardarlo en user_profiles
            // Como no es AUTO_INCREMENT, lo buscamos por el email (que es UNIQUE)
            $stmtGetId = $this->db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmtGetId->execute([$email]);
            $insertedIdBin = $stmtGetId->fetchColumn();

            // Insertar perfil
            $stmtProfile = $this->db->prepare("INSERT INTO user_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmtProfile->execute([$insertedIdBin, $firstName, $lastName]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            // En entorno local se puede hacer echo o log del error para depurar
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Busca al usuario durante el login por su email.
     * Retorna los datos con el ID convertido a hexadecimal texto.
     *
     * @param string $email
     * @return array|false Datos del usuario o false si no existe.
     */
    public function getByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.email, u.password_hash, u.tier_id, u.status, p.first_name, p.last_name
            FROM users u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.email = ? LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Regla Crítica de UUID: convertir BINARY(16) a texto con bin2hex()
            $user['id_hex'] = self::binToUuid($user['id']);
            return $user;
        }

        return false;
    }
}
