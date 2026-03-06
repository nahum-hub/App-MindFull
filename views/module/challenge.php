<?php
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto: <?= htmlspecialchars($challenge['title']) ?> - Círculo de Crecimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen font-sans text-gray-900">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="<?= BASE_URL ?>" class="text-xl font-bold text-blue-600 tracking-tight">Círculo de Crecimiento</a>
                    <div class="hidden md:flex space-x-6">
                        <a href="<?= BASE_URL ?>module/dashboard" class="text-gray-500 hover:text-blue-600 font-medium transition">Dashboard</a>
                        <a href="<?= BASE_URL ?>mi-progreso" class="text-gray-500 hover:text-blue-600 font-medium transition">Mi Progreso</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?= BASE_URL ?>logout" class="bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 px-4 py-2 rounded-md text-sm font-medium transition">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-4xl mx-auto px-4 sm:px-6 py-10 w-full">

        <div class="mb-6">
            <a href="<?= BASE_URL ?>module/dashboard" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center">
                &larr; Volver al Dashboard
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <!-- Header -->
            <div class="px-6 py-8 border-b border-gray-100 bg-blue-50">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 uppercase tracking-wide mb-3">
                    <?= htmlspecialchars(str_replace('reto_', '', $challenge['type'])) ?>
                </span>
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2"><?= htmlspecialchars($challenge['title']) ?></h1>
                <p class="text-gray-500 text-lg leading-relaxed"><?= nl2br(htmlspecialchars($challenge['description'])) ?></p>
            </div>

            <!-- Content -->
            <div class="px-6 py-8">
                <?php if ($isFinished): ?>
                    <div class="text-center py-10">
                        <span class="text-6xl block mb-4">🏆</span>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">¡Reto Completado!</h2>
                        <p class="text-gray-600">Has finalizado todas las actividades de este reto con éxito.</p>
                    </div>
                <?php else: ?>
                    <!-- Indicador de Día -->
                    <div class="mb-8 text-center">
                        <span class="inline-block bg-blue-100 text-blue-800 text-sm font-bold px-4 py-2 rounded-full">
                            Día <?= $currentDay ?>
                        </span>
                    </div>

                    <?php if ($todayActivity): ?>
                        <?php $isDone = ($todayActivity['is_completed'] === 0); ?>
                        <div class="bg-gray-50 p-6 rounded-lg shadow-inner border <?= $isDone ? 'border-green-200' : 'border-gray-200' ?>">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                                    Pregunta del Día:
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

                            <p class="text-gray-700 mb-6 font-medium text-lg"><?= nl2br(htmlspecialchars($todayActivity['content_text'])) ?></p>

                            <?php if (!$isDone): ?>
                                <form class="activity-form" data-activity-id="<?= $todayActivity['id'] ?>">
                                    <textarea
                                        name="response"
                                        rows="4"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-3 text-gray-700 bg-white"
                                        placeholder="Reflexiona y escribe tu respuesta aquí..."
                                    ><?= htmlspecialchars($todayActivity['response_content'] ?? '') ?></textarea>

                                    <div class="mt-4 flex justify-end items-center space-x-4">
                                        <span class="status-msg text-sm text-green-600 hidden font-medium">✓ Guardado</span>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md shadow-sm transition">
                                            Guardar Respuesta
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="bg-green-50 border border-green-200 p-4 rounded-md">
                                    <p class="text-green-800 italic">"<?= nl2br(htmlspecialchars($todayActivity['response_content'])) ?>"</p>
                                </div>
                                <div class="mt-6 text-center">
                                    <p class="text-gray-500 font-medium">Mañana se desbloquea tu siguiente paso de gratitud. ¡Sigue así!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-4xl block mb-4">⏳</span>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Has completado las actividades de hoy</h3>
                            <p class="text-gray-600">Mañana se desbloquea tu siguiente paso de gratitud. ¡Sigue así!</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

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
                                    location.reload();
                                }, 1500);
                            } else {
                                console.log("Error del servidor:", data.error || "Desconocido");
                                alert('Hubo un error al guardar tu respuesta. Revisa la consola para más detalles.');
                                btn.prop('disabled', false).text('Guardar Respuesta');
                            }
                        } catch (err) {
                            console.error("Error procesando JSON:", res);
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