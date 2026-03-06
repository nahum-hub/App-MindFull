<?php

$host = '127.0.0.1';
$db   = 'circulo_crecimiento';
$user = 'jules';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

/**
 * Convierte un UUID (hexadecimal con o sin guiones) a BINARY(16)
 *
 * @param string $uuid
 * @return string
 */
function uuidToBin($uuid) {
    // Elimina guiones si existen
    $hex = str_replace('-', '', $uuid);
    return hex2bin($hex);
}

/**
 * Convierte un BINARY(16) a UUID (hexadecimal)
 *
 * @param string $bin
 * @return string
 */
function binToUuid($bin) {
    return bin2hex($bin);
}

/**
 * Genera un nuevo UUID v4 (versión simple sin guiones)
 *
 * @return string
 */
function generateUuidHex() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return bin2hex($data);
}
