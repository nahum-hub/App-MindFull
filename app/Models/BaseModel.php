<?php

namespace App\Models;

use Config\Config;

abstract class BaseModel {
    protected $db;

    public function __construct() {
        // Inicializa la conexión en el constructor base de cada modelo
        $this->db = Config::getConnection();
    }

    /**
     * Convierte UUID string hexadecimal a formato binario para consultas
     */
    protected function stringToBin($uuid) {
        return Config::uuidToBin($uuid);
    }

    /**
     * Convierte de formato binario SQL a UUID string hexadecimal
     */
    protected function binToString($bin) {
        return Config::binToUuid($bin);
    }
}
