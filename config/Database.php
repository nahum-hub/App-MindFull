<?php

namespace Config;

class Database {
    private const HOST = 'localhost';
    private const USER = 'root';
    private const PASS = '';
    private const DBNAME = 'personal_growth_db';
    private const CHARSET = 'utf8mb4';

    private static $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection() {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::HOST . ";dbname=" . self::DBNAME . ";charset=" . self::CHARSET;
            $options = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // Activado para depurar
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new \PDO($dsn, self::USER, self::PASS, $options);
            } catch (\PDOException $e) {
                // En producción esto debería loguearse, en local mostramos el error
                die("Connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
