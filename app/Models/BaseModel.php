<?php

namespace App\Models;

use Config\Database;

abstract class BaseModel {
    protected $db;

    public function __construct() {
        // Inicializa la conexión en el constructor base de cada modelo
        $this->db = Database::getConnection();
    }

    /**
     * Convierte UUID string (con o sin guiones) a BINARY(16)
     *
     * @param string $uuid
     * @return string
     */
    public static function uuidToBin($uuid) {
        // Remueve guiones en caso de que vengan en el formato string
        $hex = str_replace('-', '', $uuid);
        return hex2bin($hex);
    }

    /**
     * Convierte BINARY(16) a UUID hexadecimal string
     *
     * @param string $bin
     * @return string
     */
    public static function binToUuid($bin) {
        return bin2hex($bin);
    }
}
