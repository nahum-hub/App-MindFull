<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id_hex = $_SESSION['user_id'];
$user_id_bin = hex2bin($user_id_hex);

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT u.*, t.name as tier_name, p.first_name FROM users u JOIN subscription_tiers t ON u.tier_id = t.id JOIN user_profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->execute([$user_id_bin]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Obtener los módulos
// Un usuario ve módulos si su tier lo permite y si los ha desbloqueado.
// Para simplificar: mostramos todos los activos, pero indicamos su estado de progreso.
// Los retos individuales se muestran si el usuario los tiene asignados.

$stmt_modules = $pdo->prepare("
    SELECT mc.*,
           ump.is_completed AS progress_completed,
           ump.started_at,
           (SELECT COUNT(*) FROM activities a WHERE a.module_id = mc.id) as total_activities,
           (SELECT COUNT(*) FROM user_activity_responses uar
            JOIN activities a ON uar.activity_id = a.id
            WHERE uar.user_id = ? AND a.module_id = mc.id AND uar.is_completed = 0) as completed_activities
    FROM modules_challenges mc
    LEFT JOIN user_module_progress ump ON mc.id = ump.module_id AND ump.user_id = ?
    WHERE mc.active = 1
      AND (mc.type != 'reto_individual'
           OR mc.id IN (SELECT challenge_id FROM individual_assignments WHERE user_id = ? AND is_active = 1))
    ORDER BY mc.numeric_order ASC
");
$stmt_modules->execute([$user_id_bin, $user_id_bin, $user_id_bin]);
$modules = $stmt_modules->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Círculo de Crecimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-gray-800">Círculo de Crecimiento</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-4 text-gray-600">Hola, <?= htmlspecialchars($user['first_name']) ?> (Tier: <?= htmlspecialchars($user['tier_name']) ?>)</span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800 font-medium">Salir</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Tus Módulos y Retos</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($modules as $mod): ?>
                <?php
                $status_text = 'No iniciado';
                $status_color = 'bg-gray-200 text-gray-800';

                if ($mod['progress_completed'] === 0) { // 0 = Completado
                    $status_text = 'Completado';
                    $status_color = 'bg-green-100 text-green-800';
                } elseif ($mod['progress_completed'] === 1) { // 1 = Pendiente
                    $status_text = 'En progreso';
                    $status_color = 'bg-blue-100 text-blue-800';

                    // Comprobar retraso (Semáforo)
                    if ($mod['started_at']) {
                        $started_date = new DateTime($mod['started_at']);
                        $now = new DateTime();
                        $diff = $now->diff($started_date)->days;
                        if ($diff > $mod['duration_days']) {
                            $status_text = 'Retraso';
                            $status_color = 'bg-red-100 text-red-800';
                        }
                    }
                }

                // Botón de acción
                $btn_text = ($status_text === 'No iniciado') ? 'Comenzar' : 'Continuar';
                if ($status_text === 'Completado') $btn_text = 'Repasar';

                // Premium restriction (simplificado)
                $can_access = true;
                if ($mod['is_premium'] && strtolower($user['tier_name']) === 'free') {
                    $can_access = false;
                    $status_text = 'Premium';
                    $status_color = 'bg-yellow-100 text-yellow-800';
                }
                ?>
                <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                    <div class="p-6 flex-grow">
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($mod['title']) ?></h3>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_color ?>">
                                <?= $status_text ?>
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 line-clamp-3">
                            <?= htmlspecialchars($mod['description']) ?>
                        </p>
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">
                                Actividades: <?= $mod['completed_activities'] ?> / <?= $mod['total_activities'] ?>
                            </p>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                                <?php $percent = $mod['total_activities'] > 0 ? ($mod['completed_activities'] / $mod['total_activities']) * 100 : 0; ?>
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $percent ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <?php if ($can_access): ?>
                            <form method="POST" action="start_module.php" class="w-full">
                                <input type="hidden" name="module_id" value="<?= $mod['id'] ?>">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <?= $btn_text ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-400 cursor-not-allowed">
                                Disponible en Pro/Ultra
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
