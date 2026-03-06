<?php
// Asegurar que las variables pasadas desde el controlador estén disponibles
$currentModule = $currentModule ?? null;
$activities = $activities ?? [];
$allCompleted = $allCompleted ?? false;
$msg = $_GET['msg'] ?? ($msg ?? null);
$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Círculo de Crecimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen font-sans text-gray-900">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>" class="text-xl font-bold text-blue-600 tracking-tight">Círculo de Crecimiento</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-500 text-sm">Mi Progreso</span>
                    <a href="<?= BASE_URL ?>logout" class="bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 px-4 py-2 rounded-md text-sm font-medium transition">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-4xl mx-auto px-4 sm:px-6 py-10 w-full">

        <?php if ($msg === 'unlocked'): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm">
                <p class="font-bold">¡Módulo desbloqueado!</p>
                <p>Has avanzado al siguiente nivel.</p>
            </div>
        <?php elseif ($msg): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded-md shadow-sm">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($error === 'wrong_keyword'): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm">
                <p class="font-bold">Palabra clave incorrecta.</p>
                <p>Por favor, revisa tus notas y vuelve a intentarlo.</p>
            </div>
        <?php endif; ?>

        <?php if ($currentModule): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <!-- Module Header -->
                <div class="px-6 py-8 border-b border-gray-100">
                    <h1 class="text-3xl font-extrabold text-gray-900 mb-2"><?= htmlspecialchars($currentModule['title']) ?></h1>
                    <p class="text-gray-500 text-lg leading-relaxed"><?= nl2br(htmlspecialchars($currentModule['description'])) ?></p>
                </div>

                <!-- Reproductor de Audio (Google Drive) -->
                <?php if (!empty($currentModule['audio_url'])): ?>
                    <?php
                    // Lógica para convertir link compartido a URL de preview/stream embebible
                    $audioUrl = $currentModule['audio_url'];
                    $audioId = '';

                    if (preg_match('/d\/([a-zA-Z0-9_-]+)/', $audioUrl, $matches)) {
                        $audioId = $matches[1];
                    } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $audioUrl, $matches)) {
                        $audioId = $matches[1];
                    }

                    if ($audioId):
                    ?>
                    <div class="px-6 py-6 bg-gray-50 border-b border-gray-100 flex flex-col items-center">
                        <span class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wider">Audio Guía</span>
                        <iframe src="https://drive.google.com/file/d/<?= htmlspecialchars($audioId) ?>/preview" width="100%" height="100" class="rounded border-0 shadow-sm" allow="autoplay"></iframe>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Lista de Actividades -->
                <div class="px-6 py-6 space-y-8 bg-gray-50">
                    <?php if (empty($activities)): ?>
                        <p class="text-gray-500 text-center italic">No hay actividades configuradas para este módulo aún.</p>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                            <?php
                                // Recordar regla: is_completed = 1 significa Pendiente. 0 significa Completado.
                                $isDone = ($act['is_completed'] === 0);
                            ?>
                            <div class="bg-white p-6 rounded-lg shadow-sm border <?= $isDone ? 'border-green-200' : 'border-gray-200' ?>">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                                        <span class="flex items-center justify-center bg-blue-100 text-blue-700 rounded-full h-8 w-8 text-sm mr-3">
                                            <?= $act['position'] ?>
                                        </span>
                                        Pregunta para ti:
                                    </h3>
                                    <?php if ($isDone): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completada
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pendiente
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-gray-700 mb-4 ml-11"><?= nl2br(htmlspecialchars($act['content_text'])) ?></p>

                                <form class="activity-form ml-11" data-activity-id="<?= $act['id'] ?>">
                                    <textarea
                                        name="response"
                                        rows="4"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-3 text-gray-700 <?= $isDone ? 'bg-green-50' : 'bg-white' ?>"
                                        placeholder="Reflexiona y escribe tu respuesta aquí..."
                                    ><?= htmlspecialchars($act['response_content'] ?? '') ?></textarea>

                                    <div class="mt-3 flex justify-end items-center space-x-4">
                                        <span class="status-msg text-sm text-green-600 hidden font-medium">✓ Guardado (Actualizado a 0)</span>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-md shadow-sm transition">
                                            Guardar Respuesta
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Formulario Keyword Unlock (Aparece sólo si todo es 0) -->
                <?php if (!empty($activities) && $allCompleted): ?>
                    <div class="px-6 py-8 bg-blue-50 border-t border-blue-100">
                        <div class="text-center mb-6">
                            <h2 class="text-2xl font-bold text-blue-900 mb-2">¡Excelente Trabajo!</h2>
                            <p class="text-blue-700">Has completado todas las actividades del módulo. Ingresa la palabra clave de la lección para avanzar.</p>
                        </div>
                        <form method="POST" action="<?= BASE_URL ?>module/unlock" class="max-w-md mx-auto flex gap-3">
                            <input type="hidden" name="module_id" value="<?= $currentModule['id'] ?>">
                            <input type="text" name="keyword" placeholder="Escribe la palabra secreta" class="flex-grow rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-3" required>
                            <button type="submit" class="bg-blue-800 hover:bg-blue-900 text-white font-bold py-3 px-6 rounded-md shadow-md transition">
                                Desbloquear
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Caso: No hay módulos (o ya terminó todo y no hay nuevos) -->
            <?php if (!$msg): ?>
                <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 text-center">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Aún no tienes módulos asignados</h2>
                    <p class="text-gray-600">Por favor, espera a que el administrador asigne nuevos retos a tu cuenta.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer class="bg-white border-t border-gray-200 py-6 text-center mt-auto">
        <p class="text-gray-500 text-sm">
            &copy; <?= date('Y') ?> Círculo de Crecimiento. Todos los derechos reservados.
        </p>
    </footer>

    <script>
        $(document).ready(function() {
            $('.activity-form').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const activityId = form.data('activity-id');
                const responseContent = form.find('textarea[name="response"]').val();
                const btn = form.find('button[type="submit"]');
                const statusMsg = form.find('.status-msg');

                if (responseContent.trim() === '') return;

                // Deshabilitar botón
                btn.prop('disabled', true).text('Guardando...');

                $.ajax({
                    url: '<?= BASE_URL ?>module/saveActivity',
                    type: 'POST',
                    data: {
                        activity_id: activityId,
                        response: responseContent
                    },
                    success: function(res) {
                        try {
                            const data = JSON.parse(res);
                            if (data.success) {
                                statusMsg.removeClass('hidden').fadeIn();
                                form.find('textarea').addClass('bg-green-50');
                                setTimeout(() => {
                                    // Recarga la página para evaluar si todo está completado (regla del Keyword Unlock)
                                    location.reload();
                                }, 1500);
                            } else {
                                alert('Hubo un error al guardar tu respuesta.');
                                btn.prop('disabled', false).text('Guardar Respuesta');
                            }
                        } catch (err) {
                            console.error(res);
                            alert('Error procesando la solicitud.');
                            btn.prop('disabled', false).text('Guardar Respuesta');
                        }
                    },
                    error: function() {
                        alert('Error de conexión.');
                        btn.prop('disabled', false).text('Guardar Respuesta');
                    }
                });
            });
        });
    </script>
</body>
</html>
