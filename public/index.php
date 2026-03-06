<?php

// Autoloader básico para PSR-4
spl_autoload_register(function ($class) {
    // Definimos los prefijos y directorios base
    $prefixes = [
        'App\\' => '../app/',
        'Config\\' => '../config/'
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        // Verifica si la clase utiliza el prefijo actual
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        // Obtiene el nombre relativo de la clase
        $relative_class = substr($class, $len);

        // Reemplaza los separadores de namespace por separadores de directorio y añade .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Si el archivo existe, lo requerimos
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

session_start();

// Determinar dinámicamente el BASE_URL en base al script actual (public/index.php)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Quitar '/public' del final si está presente
$baseDir = preg_replace('#/public/?$#', '', $scriptDir);
if ($baseDir === '/' || $baseDir === '') {
    $baseDir = '';
}
define('BASE_URL', rtrim($baseDir, '/') . '/');

// Front Controller Básico con Mapeo de Rutas Especiales
// Apache/XAMPP .htaccess pasa la ruta limpia a través de $_GET['url']
$url = $_GET['url'] ?? 'home';
$url = rtrim($url, '/');

if (empty($url)) {
    $url = 'home';
}
$urlParts = explode('/', filter_var($url, FILTER_SANITIZE_URL));

// Mapeo de rutas amigables
$routes = [
    'login' => ['AuthController', 'login'],
    'register' => ['AuthController', 'register'],
    'logout' => ['AuthController', 'logout'],
    'home' => ['HomeController', 'index'],
    'continue' => ['HomeController', 'continueProgreso'],
    'dashboard' => ['ModuleController', 'dashboard'] // Por si se accede como /dashboard
];

$firstSegment = strtolower($urlParts[0]);

// Verificar si es un caso especial anidado, ej. module/dashboard -> Controller: ModuleController, Method: dashboard
if ($firstSegment === 'module' && isset($urlParts[1])) {
    $controllerName = 'ModuleController';
    $methodName = $urlParts[1];
    unset($urlParts[0], $urlParts[1]);
    $params = array_values($urlParts);
} elseif (isset($routes[$firstSegment])) {
    $controllerName = $routes[$firstSegment][0];
    $methodName = $routes[$firstSegment][1];

    // Si hay más partes en la URL, se pasan como parámetros (por si acaso se necesitan)
    unset($urlParts[0]);
    $params = array_values($urlParts);
} else {
    // Comportamiento dinámico por defecto
    $controllerName = (!empty($urlParts[0])) ? ucfirst($urlParts[0]) . 'Controller' : 'HomeController';
    $methodName = (!empty($urlParts[1])) ? $urlParts[1] : 'index';

    unset($urlParts[0], $urlParts[1]);
    $params = array_values($urlParts);
}

// Path al controlador
$controllerFile = '../app/Controllers/' . $controllerName . '.php';

// Verificamos si el archivo del controlador existe
if (file_exists($controllerFile)) {
    // Instanciamos el controlador
    $controllerClass = '\\App\\Controllers\\' . $controllerName;
    $controller = new $controllerClass();

    // Verificamos si el método existe en el controlador
    if (method_exists($controller, $methodName)) {
        // Llamamos al método con sus parámetros
        call_user_func_array([$controller, $methodName], $params ?? []);
    } else {
        // En producción sería un render de vista 404, en local depuramos:
        die("Error 404: Método '$methodName' no encontrado en el controlador '$controllerName'.");
    }
} else {
    // Si no existe, podemos redirigir a un Default/HomeController o dar error
    die("Error 404: Controlador '$controllerName' no encontrado.");
}
