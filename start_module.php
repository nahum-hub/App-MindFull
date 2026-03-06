<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id_hex = $_SESSION['user_id'];
$user_id_bin = hex2bin($user_id_hex);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['module_id'])) {
    $module_id = (int)$_POST['module_id'];

    // Verificar si el progreso existe
    $stmt = $pdo->prepare("SELECT id FROM user_module_progress WHERE user_id = ? AND module_id = ?");
    $stmt->execute([$user_id_bin, $module_id]);
    $progress = $stmt->fetch();

    if (!$progress) {
        $stmt_insert = $pdo->prepare("INSERT INTO user_module_progress (user_id, module_id, started_at, is_completed) VALUES (?, ?, NOW(), 1)");
        $stmt_insert->execute([$user_id_bin, $module_id]);
    }

    header('Location: module.php?id=' . $module_id);
    exit;
} else {
    header('Location: index.php');
    exit;
}
