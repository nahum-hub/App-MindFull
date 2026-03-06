<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id_hex = $_SESSION['user_id'];
$user_id_bin = hex2bin($user_id_hex);

// Verificar si el usuario es Admin (Para simplificar, asumimos que Tier = 3 es Admin/Ultra)
$stmt_admin = $pdo->prepare("SELECT tier_id FROM users WHERE id = ?");
$stmt_admin->execute([$user_id_bin]);
$admin = $stmt_admin->fetch();

if (!$admin || $admin['tier_id'] != 3) {
    die("Acceso denegado. Se requiere nivel de administrador.");
}

// Procesar acciones del admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $target_user_id = isset($_POST['target_user_id']) ? hex2bin($_POST['target_user_id']) : null;

        if ($action === 'change_tier' && $target_user_id && isset($_POST['new_tier_id'])) {
            $new_tier_id = (int)$_POST['new_tier_id'];
            $stmt = $pdo->prepare("UPDATE users SET tier_id = ? WHERE id = ?");
            $stmt->execute([$new_tier_id, $target_user_id]);
            $msg = "Nivel de suscripción actualizado.";
        }

        if ($action === 'assign_challenge' && $target_user_id && isset($_POST['challenge_id'])) {
            $challenge_id = (int)$_POST['challenge_id'];
            // Verificar si ya existe
            $stmt_check = $pdo->prepare("SELECT id FROM individual_assignments WHERE user_id = ? AND challenge_id = ?");
            $stmt_check->execute([$target_user_id, $challenge_id]);
            if (!$stmt_check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO individual_assignments (user_id, challenge_id) VALUES (?, ?)");
                $stmt->execute([$target_user_id, $challenge_id]);
                $msg = "Reto asignado correctamente.";
            } else {
                $error = "El usuario ya tiene este reto asignado.";
            }
        }
    }
}

// Obtener todos los usuarios con su información de perfil y nivel
$stmt_users = $pdo->prepare("
    SELECT u.id, bin2hex(u.id) as id_hex, u.email, u.tier_id, p.first_name, p.last_name, t.name as tier_name
    FROM users u
    JOIN user_profiles p ON u.id = p.user_id
    JOIN subscription_tiers t ON u.tier_id = t.id
    ORDER BY p.first_name ASC
");
$stmt_users->execute();
$users = $stmt_users->fetchAll();

// Obtener todos los tiers para el formulario
$stmt_tiers = $pdo->prepare("SELECT id, name FROM subscription_tiers ORDER BY id ASC");
$stmt_tiers->execute();
$tiers = $stmt_tiers->fetchAll();

// Obtener todos los retos individuales
$stmt_challenges = $pdo->prepare("SELECT id, title FROM modules_challenges WHERE type = 'reto_individual' AND active = 1");
$stmt_challenges->execute();
$challenges = $stmt_challenges->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .details-row { display: none; }
        .details-row.active { display: table-row; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Panel de Administración</h1>
            <a href="index.php" class="text-blue-600 hover:text-blue-800">Volver al Dashboard</a>
        </div>

        <?php if (isset($msg)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semáforo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <?php
                        // Verificar semáforo de cumplimiento para este usuario
                        // Retraso si is_completed = 1 y la fecha actual > started_at + duration_days
                        $user_bin = hex2bin($user['id_hex']);
                        $stmt_prog = $pdo->prepare("
                            SELECT ump.module_id, mc.title, ump.started_at, mc.duration_days
                            FROM user_module_progress ump
                            JOIN modules_challenges mc ON ump.module_id = mc.id
                            WHERE ump.user_id = ? AND ump.is_completed = 1
                        ");
                        $stmt_prog->execute([$user_bin]);
                        $pending_modules = $stmt_prog->fetchAll();

                        $has_delay = false;
                        $delay_modules = [];
                        foreach ($pending_modules as $pm) {
                            if ($pm['started_at'] && $pm['duration_days']) {
                                $start_date = new DateTime($pm['started_at']);
                                $now = new DateTime();
                                $diff = $now->diff($start_date)->days;
                                if ($diff > $pm['duration_days']) {
                                    $has_delay = true;
                                    $delay_modules[] = $pm['title'];
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($user['tier_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($has_delay): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" title="Retraso en: <?= htmlspecialchars(implode(', ', $delay_modules)) ?>">
                                        Retraso
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Al día
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="toggleDetails('<?= $user['id_hex'] ?>')" class="text-indigo-600 hover:text-indigo-900 mr-4">Gestionar</button>
                            </td>
                        </tr>
                        <!-- Fila expandible con detalles y formularios -->
                        <tr id="details-<?= $user['id_hex'] ?>" class="details-row bg-gray-50">
                            <td colspan="5" class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Cambiar Nivel -->
                                    <div class="bg-white p-4 shadow rounded">
                                        <h4 class="font-bold text-gray-800 mb-2">Cambiar Nivel de Suscripción</h4>
                                        <form method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="action" value="change_tier">
                                            <input type="hidden" name="target_user_id" value="<?= $user['id_hex'] ?>">
                                            <select name="new_tier_id" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <?php foreach ($tiers as $tier): ?>
                                                    <option value="<?= $tier['id'] ?>" <?= $tier['id'] == $user['tier_id'] ? 'selected' : '' ?>><?= htmlspecialchars($tier['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Actualizar</button>
                                        </form>
                                    </div>
                                    <!-- Asignar Reto -->
                                    <div class="bg-white p-4 shadow rounded">
                                        <h4 class="font-bold text-gray-800 mb-2">Asignar Reto Individual</h4>
                                        <?php if (count($challenges) > 0): ?>
                                            <form method="POST" class="flex items-center gap-2">
                                                <input type="hidden" name="action" value="assign_challenge">
                                                <input type="hidden" name="target_user_id" value="<?= $user['id_hex'] ?>">
                                                <select name="challenge_id" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                    <?php foreach ($challenges as $ch): ?>
                                                        <option value="<?= $ch['id'] ?>"><?= htmlspecialchars($ch['title']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">Asignar</button>
                                            </form>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-500">No hay retos individuales creados en la base de datos.</p>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Ver Respuestas -->
                                    <div class="col-span-1 md:col-span-2 bg-white p-4 shadow rounded mt-4">
                                        <h4 class="font-bold text-gray-800 mb-2">Respuestas de Actividades</h4>
                                        <?php
                                        // Obtener respuestas del usuario
                                        $stmt_resp = $pdo->prepare("
                                            SELECT a.content_text, uar.response_content, mc.title as module_title
                                            FROM user_activity_responses uar
                                            JOIN activities a ON uar.activity_id = a.id
                                            JOIN modules_challenges mc ON a.module_id = mc.id
                                            WHERE uar.user_id = ?
                                            ORDER BY mc.id ASC, a.position ASC
                                        ");
                                        $stmt_resp->execute([$user_bin]);
                                        $responses = $stmt_resp->fetchAll();
                                        ?>
                                        <?php if (count($responses) > 0): ?>
                                            <ul class="divide-y divide-gray-200 h-64 overflow-y-auto">
                                                <?php foreach ($responses as $r): ?>
                                                    <li class="py-4">
                                                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($r['module_title']) ?> - <span class="text-gray-500"><?= htmlspecialchars($r['content_text']) ?></span></p>
                                                        <p class="text-sm text-gray-700 mt-1 bg-gray-50 p-2 rounded border"><?= nl2br(htmlspecialchars($r['response_content'])) ?></p>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-500">El usuario no ha respondido ninguna actividad aún.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleDetails(userIdHex) {
            const row = document.getElementById('details-' + userIdHex);
            if (row.classList.contains('active')) {
                row.classList.remove('active');
            } else {
                // Opcional: Cerrar otras filas
                document.querySelectorAll('.details-row').forEach(r => r.classList.remove('active'));
                row.classList.add('active');
            }
        }
    </script>
</body>
</html>
