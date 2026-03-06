<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Progreso - Círculo de Crecimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        <a href="<?= BASE_URL ?>mi-progreso" class="text-blue-600 font-bold border-b-2 border-blue-600 px-1 py-5">Mi Progreso</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?= BASE_URL ?>logout" class="bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 px-4 py-2 rounded-md font-medium transition">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-4 flex justify-center items-center">
                <span class="mr-3 text-4xl">🏆</span> Muro de Honor
            </h1>
            <p class="text-xl text-gray-600">Celebra cada paso en tu viaje de desarrollo personal.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Columna de Módulos Completados -->
            <div class="bg-white rounded-xl shadow-md border border-yellow-200 overflow-hidden">
                <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-100">
                    <h2 class="text-2xl font-bold text-yellow-900">Módulos Completados</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($modulosTerminados)): ?>
                        <div class="text-center py-8">
                            <span class="text-4xl block mb-2 opacity-50">📚</span>
                            <p class="text-gray-500 italic">Aún no has completado ningún módulo principal.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($modulosTerminados as $mod): ?>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100 hover:shadow-sm transition flex items-start">
                                    <span class="text-2xl mr-4" title="Completado">⭐</span>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($mod['title']) ?></h3>
                                        <?php if (!empty($mod['completed_at'])): ?>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Completado el <?= date('d M Y', strtotime($mod['completed_at'])) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna de Retos Completados -->
            <div class="bg-white rounded-xl shadow-md border border-blue-200 overflow-hidden">
                <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                    <h2 class="text-2xl font-bold text-blue-900">Retos Completados</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($retosTerminados)): ?>
                        <div class="text-center py-8">
                            <span class="text-4xl block mb-2 opacity-50">🎯</span>
                            <p class="text-gray-500 italic">Aún no has completado ningún reto.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($retosTerminados as $reto): ?>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100 hover:shadow-sm transition flex items-start">
                                    <span class="text-2xl mr-4" title="Completado">⭐</span>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($reto['title']) ?></h3>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 uppercase tracking-wide">
                                            <?= htmlspecialchars(str_replace('reto_', '', $reto['type'])) ?>
                                        </span>
                                        <?php if (!empty($reto['completed_at'])): ?>
                                            <p class="text-xs text-gray-500 mt-2">
                                                <?= date('d M Y', strtotime($reto['completed_at'])) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 text-center mt-auto">
        <p class="text-gray-500 text-sm">
            &copy; <?= date('Y') ?> Círculo de Crecimiento. Todos los derechos reservados.
        </p>
    </footer>

</body>
</html>