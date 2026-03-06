<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $user_id_hex = bin2hex($user['id']);
            $_SESSION['user_id'] = $user_id_hex;

            // Encontrar el módulo pendiente más antiguo
            $stmt_oldest = $pdo->prepare("
                SELECT module_id
                FROM user_module_progress
                WHERE user_id = ? AND is_completed = 1
                ORDER BY started_at ASC
                LIMIT 1
            ");
            $stmt_oldest->execute([$user['id']]);
            $oldest_module = $stmt_oldest->fetch();

            if ($oldest_module) {
                header('Location: module.php?id=' . $oldest_module['module_id']);
                exit;
            } else {
                header('Location: index.php');
                exit;
            }
        } else {
            $error = "Credenciales incorrectas.";
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Círculo de Crecimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Círculo de Crecimiento</h2>
        <h3 class="text-xl mb-6 text-center text-gray-600">Iniciar Sesión</h3>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Correo Electrónico</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Contraseña</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Entrar
                </button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="register.php">
                    Registrarse
                </a>
            </div>
        </form>
    </div>
</body>
</html>
