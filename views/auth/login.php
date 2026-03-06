<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Círculo de Crecimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-100">
        <h2 class="text-3xl font-extrabold mb-2 text-center text-gray-900 tracking-tight">Círculo de Crecimiento</h2>
        <h3 class="text-lg mb-8 text-center text-gray-500 font-medium">Bienvenido de nuevo</h3>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'registered'): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm" role="alert">
                <p class="font-bold">¡Registro exitoso!</p>
                <p>Ahora puedes iniciar sesión con tus credenciales.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/auth/login" class="space-y-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="email">Correo Electrónico</label>
                <input class="appearance-none border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" id="email" name="email" type="email" placeholder="tu@email.com" required>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">Contraseña</label>
                <input class="appearance-none border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" id="password" name="password" type="password" placeholder="••••••••" required>
            </div>
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        Recordarme
                    </label>
                </div>
                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition">¿Olvidaste tu contraseña?</a>
                </div>
            </div>
            <div>
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition shadow-md" type="submit">
                    Entrar
                </button>
            </div>
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    ¿No tienes cuenta?
                    <a href="/auth/showRegister" class="font-semibold text-blue-600 hover:text-blue-500 transition">
                        Regístrate aquí
                    </a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>
