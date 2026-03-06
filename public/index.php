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

// Front Controller Básico
$url = $_GET['url'] ?? 'home';
$url = rtrim($url, '/');
$urlParts = explode('/', filter_var($url, FILTER_SANITIZE_URL));

// Capitalizamos el primer segmento para el controlador (ej. 'home' -> 'HomeController')
$controllerName = ucfirst($urlParts[0]) . 'Controller';

// El segundo segmento es el método (por defecto 'index')
$methodName = isset($urlParts[1]) ? $urlParts[1] : 'index';

// Path al controlador
$controllerFile = '../app/Controllers/' . $controllerName . '.php';

// Verificamos si el archivo del controlador existe
if (file_exists($controllerFile)) {
    // Instanciamos el controlador
    $controllerClass = '\\App\\Controllers\\' . $controllerName;
    $controller = new $controllerClass();

    // Verificamos si el método existe en el controlador
    if (method_exists($controller, $methodName)) {
        // Eliminamos controlador y método del array para pasar el resto como parámetros
        unset($urlParts[0], $urlParts[1]);
        $params = array_values($urlParts);

        // Llamamos al método con sus parámetros
        call_user_func_array([$controller, $methodName], $params);
    } else {
        // En producción sería un render de vista 404, en local depuramos:
        die("Error 404: Método '$methodName' no encontrado en el controlador '$controllerName'.");
    }
} else {
    // Si no existe, podemos redirigir a un Default/HomeController o dar error
    die("Error 404: Controlador '$controllerName' no encontrado.");
}
