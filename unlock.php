<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id_hex = $_SESSION['user_id'];
    $user_id_bin = hex2bin($user_id_hex);
    $module_id = isset($_POST['module_id']) ? (int)$_POST['module_id'] : 0;
    $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';

    if ($module_id === 0) {
        header('Location: index.php');
        exit;
    }

    // Verificar el módulo y la palabra clave
    $stmt = $pdo->prepare("SELECT unlock_keyword FROM modules_challenges WHERE id = ?");
    $stmt->execute([$module_id]);
    $module = $stmt->fetch();

    if ($module) {
        // Verificar si la palabra clave coincide (o si el módulo no requiere palabra clave)
        $expected_keyword = $module['unlock_keyword'];

        if (empty($expected_keyword) || strcasecmp($keyword, $expected_keyword) === 0) {
            // Asegurar que el progreso se marca como completado incluso si no existía el registro previo
            $stmt_check = $pdo->prepare("SELECT id FROM user_module_progress WHERE user_id = ? AND module_id = ?");
            $stmt_check->execute([$user_id_bin, $module_id]);

            if ($stmt_check->fetch()) {
                $stmt_update = $pdo->prepare("UPDATE user_module_progress SET is_completed = 0, completed_at = NOW() WHERE user_id = ? AND module_id = ?");
                $stmt_update->execute([$user_id_bin, $module_id]);
            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO user_module_progress (user_id, module_id, is_completed, started_at, completed_at) VALUES (?, ?, 0, NOW(), NOW())");
                $stmt_insert->execute([$user_id_bin, $module_id]);
            }

            // Redirigir al dashboard para ver el progreso
            header('Location: index.php?msg=module_completed');
            exit;
        } else {
            // Palabra clave incorrecta
            header("Location: module.php?id=$module_id&error=invalid_keyword");
            exit;
        }
    } else {
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
