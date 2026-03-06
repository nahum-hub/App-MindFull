<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';

    if (!empty($email) && !empty($password) && !empty($first_name) && !empty($last_name)) {
        // Generar un UUID único
        $uuid_hex = generateUuidHex();
        $uuid_bin = hex2bin($uuid_hex);

        // Hashear la contraseña
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Obtener ID del Tier "Free"
        $stmt_tier = $pdo->prepare("SELECT id FROM subscription_tiers WHERE name = 'Free' LIMIT 1");
        $stmt_tier->execute();
        $tier = $stmt_tier->fetch();
        $tier_id = $tier ? $tier['id'] : 1;

        try {
            $pdo->beginTransaction();

            // Insertar en tabla users
            $stmt = $pdo->prepare("INSERT INTO users (id, email, password_hash, tier_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$uuid_bin, $email, $password_hash, $tier_id]);

            // Insertar en tabla user_profiles
            $stmt_profile = $pdo->prepare("INSERT INTO user_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmt_profile->execute([$uuid_bin, $first_name, $last_name]);

            // Iniciar sesión
            $_SESSION['user_id'] = $uuid_hex;

            $pdo->commit();

            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al registrar el usuario: " . $e->getMessage();
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
    <title>Registro - Círculo de Crecimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Círculo de Crecimiento</h2>
        <h3 class="text-xl mb-6 text-center text-gray-600">Crear Cuenta</h3>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="first_name">Nombre</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="first_name" name="first_name" type="text" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="last_name">Apellidos</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="last_name" name="last_name" type="text" required>
            </div>
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
                    Registrarse
                </button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="login.php">
                    ¿Ya tienes cuenta?
                </a>
            </div>
        </form>
    </div>
</body>
</html>
