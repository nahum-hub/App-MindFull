<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No session']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id_hex = $_SESSION['user_id'];
    $user_id_bin = hex2bin($user_id_hex);
    $activity_id = isset($_POST['activity_id']) ? (int)$_POST['activity_id'] : 0;
    $response = isset($_POST['response']) ? trim($_POST['response']) : '';

    if ($activity_id === 0 || empty($response)) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    // Verificar si ya existe una respuesta
    $stmt = $pdo->prepare("SELECT id FROM user_activity_responses WHERE user_id = ? AND activity_id = ?");
    $stmt->execute([$user_id_bin, $activity_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Actualizar respuesta y marcar como completada (is_completed = 0)
        $stmt_update = $pdo->prepare("UPDATE user_activity_responses SET response_content = ?, is_completed = 0 WHERE id = ?");
        $success = $stmt_update->execute([$response, $existing['id']]);
    } else {
        // Insertar respuesta y marcar como completada (is_completed = 0)
        $stmt_insert = $pdo->prepare("INSERT INTO user_activity_responses (user_id, activity_id, response_content, is_completed) VALUES (?, ?, ?, 0)");
        $success = $stmt_insert->execute([$user_id_bin, $activity_id, $response]);
    }

    echo json_encode(['success' => $success]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
