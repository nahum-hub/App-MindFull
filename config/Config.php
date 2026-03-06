<?php

namespace Config;

class Config {
    // Parámetros de conexión a la BD
    private const DB_HOST = '127.0.0.1';
    private const DB_NAME = 'circulo_crecimiento';
    private const DB_USER = 'jules';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    private static $pdo = null;

    /**
     * Obtiene la conexión PDO usando Singleton
     */
    public static function getConnection() {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=" . self::DB_CHARSET;
            $options = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new \PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            } catch (\PDOException $e) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    /**
     * Helper global para convertir UUID string a BINARY(16)
     */
    public static function uuidToBin($uuid) {
        $hex = str_replace('-', '', $uuid);
        return hex2bin($hex);
    }

    /**
     * Helper global para convertir BINARY(16) a UUID hexadecimal
     */
    public static function binToUuid($bin) {
        return bin2hex($bin);
    }

    /**
     * Genera un nuevo UUID Hex
     */
    public static function generateUuidHex() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // bits 6-7 = 10
        return bin2hex($data);
    }
}
