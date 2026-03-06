<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id_hex = $_SESSION['user_id'];
$user_id_bin = hex2bin($user_id_hex);

$module_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($module_id === 0) {
    header('Location: index.php');
    exit;
}

// Obtener detalles del módulo
$stmt_mod = $pdo->prepare("SELECT * FROM modules_challenges WHERE id = ?");
$stmt_mod->execute([$module_id]);
$module = $stmt_mod->fetch();

if (!$module) {
    header('Location: index.php');
    exit;
}

// Obtener actividades y respuestas del usuario (si las hay)
$stmt_acts = $pdo->prepare("
    SELECT a.id, a.content_text, a.position, uar.response_content, uar.is_completed
    FROM activities a
    LEFT JOIN user_activity_responses uar ON a.id = uar.activity_id AND uar.user_id = ?
    WHERE a.module_id = ?
    ORDER BY a.position ASC
");
$stmt_acts->execute([$user_id_bin, $module_id]);
$activities = $stmt_acts->fetchAll();

// Verificar si todas las actividades están completadas
$all_completed = true;
$total_activities = count($activities);
$completed_activities = 0;

foreach ($activities as $act) {
    if ($act['is_completed'] !== 0) { // 0 es completado en la lógica
        $all_completed = false;
    } else {
        $completed_activities++;
    }
}

// Obtener progreso del módulo
$stmt_prog = $pdo->prepare("SELECT * FROM user_module_progress WHERE user_id = ? AND module_id = ?");
$stmt_prog->execute([$user_id_bin, $module_id]);
$progress = $stmt_prog->fetch();

$module_completed = ($progress && $progress['is_completed'] == 0);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($module['title']) ?> - Círculo de Crecimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold text-gray-800 hover:text-blue-600">&larr; Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6">
                <h2 class="text-2xl leading-6 font-medium text-gray-900">
                    <?= htmlspecialchars($module['title']) ?>
                </h2>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    <?= nl2br(htmlspecialchars($module['description'])) ?>
                </p>
                <?php if ($module_completed): ?>
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800 mt-2">
                        Módulo Completado
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mt-2">
                        Progreso: <?= $completed_activities ?> / <?= $total_activities ?> Actividades
                    </span>
                <?php endif; ?>
            </div>

            <?php if (!empty($module['audio_url'])): ?>
                <?php
                // Extraer el ID del enlace de Google Drive
                $audio_id = '';
                if (preg_match('/id=([a-zA-Z0-9_-]+)/', $module['audio_url'], $matches)) {
                    $audio_id = $matches[1];
                } elseif (preg_match('/d\/([a-zA-Z0-9_-]+)/', $module['audio_url'], $matches)) {
                    $audio_id = $matches[1];
                }

                if ($audio_id):
                ?>
                <div class="px-4 py-5 border-t border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Audio del Módulo</h3>
                    <iframe src="https://drive.google.com/file/d/<?= $audio_id ?>/preview" width="100%" height="150" frameborder="0" allow="autoplay"></iframe>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="border-t border-gray-200">
                <dl>
                    <?php foreach ($activities as $index => $act): ?>
                        <div class="<?= $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' ?> px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 sm:col-span-1">
                                Actividad <?= $act['position'] ?>
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <p class="mb-3 font-medium text-gray-800"><?= htmlspecialchars($act['content_text']) ?></p>

                                <form class="activity-form" data-id="<?= $act['id'] ?>">
                                    <textarea name="response" rows="4" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border <?= $act['is_completed'] === 0 ? 'bg-green-50' : '' ?>" placeholder="Escribe tu respuesta aquí..."><?= htmlspecialchars($act['response_content'] ?? '') ?></textarea>
                                    <div class="mt-2 flex justify-end items-center">
                                        <span class="status-msg text-sm text-green-600 mr-4 hidden">Guardado automáticamente</span>
                                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Guardar Respuesta
                                        </button>
                                    </div>
                                </form>
                            </dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>

        <?php if ($all_completed && !$module_completed && !empty($module['unlock_keyword'])): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-r shadow-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Has completado todas las actividades</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Ingresa la palabra clave para desbloquear el siguiente nivel.</p>
                        </div>
                        <div class="mt-4">
                            <form action="unlock.php" method="POST" class="flex gap-2">
                                <input type="hidden" name="module_id" value="<?= $module_id ?>">
                                <input type="text" name="keyword" class="shadow-sm focus:ring-yellow-500 focus:border-yellow-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border" placeholder="Palabra clave" required>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    Desbloquear
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($all_completed && !$module_completed && empty($module['unlock_keyword'])): ?>
            <!-- Si no hay palabra clave pero está completo, completarlo automáticamente -->
            <form action="unlock.php" method="POST" class="mt-4">
                <input type="hidden" name="module_id" value="<?= $module_id ?>">
                <input type="hidden" name="keyword" value="">
                <button type="submit" class="w-full inline-flex justify-center py-3 px-4 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Finalizar Módulo
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
    $(document).ready(function() {
        $('.activity-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var activityId = form.data('id');
            var responseText = form.find('textarea').val();
            var statusMsg = form.find('.status-msg');
            var btn = form.find('button');

            btn.prop('disabled', true).text('Guardando...');

            $.ajax({
                url: 'save_activity.php',
                method: 'POST',
                data: {
                    activity_id: activityId,
                    response: responseText
                },
                success: function(res) {
                    var data = JSON.parse(res);
                    if (data.success) {
                        statusMsg.removeClass('hidden text-red-600').addClass('text-green-600').text('Guardado').show().delay(2000).fadeOut();
                        form.find('textarea').addClass('bg-green-50');
                        // Recargar página para actualizar progreso (simplificación)
                        setTimeout(function() { location.reload(); }, 1000);
                    } else {
                        statusMsg.removeClass('hidden text-green-600').addClass('text-red-600').text('Error al guardar').show().delay(3000).fadeOut();
                        btn.prop('disabled', false).text('Guardar Respuesta');
                    }
                },
                error: function() {
                    statusMsg.removeClass('hidden text-green-600').addClass('text-red-600').text('Error de conexión').show().delay(3000).fadeOut();
                    btn.prop('disabled', false).text('Guardar Respuesta');
                }
            });
        });
    });
    </script>
</body>
</html>
