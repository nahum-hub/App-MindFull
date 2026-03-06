<?php
// Evitar mostrar advertencias si la sesión ya está iniciada en el index principal,
// pero asegurarnos de tener acceso a $_SESSION si no.
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
    <title>Círculo de Crecimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= BASE_URL ?>module/dashboard" class="text-gray-600 hover:text-blue-600 font-medium transition">Ir al Dashboard</a>
                        <a href="<?= BASE_URL ?>logout" class="bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 px-4 py-2 rounded-md font-medium transition">Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>login" class="text-gray-600 hover:text-blue-600 font-medium transition">Iniciar Sesión</a>
                        <a href="<?= BASE_URL ?>register" class="bg-blue-600 text-white hover:bg-blue-700 px-5 py-2 rounded-md font-medium shadow-sm transition">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex-grow flex items-center justify-center py-20 px-4">
        <div class="max-w-3xl w-full text-center space-y-8">
            <h1 class="text-5xl font-extrabold tracking-tight text-gray-900 sm:text-6xl">
                Transforma tu vida <span class="text-blue-600 block mt-2">paso a paso</span>
            </h1>
            <p class="text-xl text-gray-500 max-w-2xl mx-auto leading-relaxed">
                Descubre el Círculo de Crecimiento. Una plataforma diseñada para guiarte a través de módulos prácticos y reflexiones profundas hacia tu mejor versión.
            </p>

            <div class="pt-6">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= BASE_URL ?>module/dashboard" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-4 px-10 rounded-full shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
                        Continuar mi progreso &rarr;
                    </a>
                <?php else: ?>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="<?= BASE_URL ?>register" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-4 px-8 rounded-full shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
                            Comenzar ahora
                        </a>
                        <a href="<?= BASE_URL ?>continue" class="inline-block bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 font-bold text-lg py-4 px-8 rounded-full shadow-sm hover:shadow transition">
                            Continuar mi progreso &rarr;
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-8 text-center mt-auto">
        <p class="text-gray-500 text-sm">
            &copy; <?= date('Y') ?> Círculo de Crecimiento. Todos los derechos reservados.
        </p>
    </footer>

</body>
</html>
