<?php

// public/index.php
// Punto de entrada principal para el patrón MVC

require_once '../config/Config.php';
require_once '../app/Models/BaseModel.php';

// Cargar variables de entorno si es necesario o iniciar sesión
session_start();

// Simple router for now, we will expand this in the next steps when creating Controllers
$url = isset($_GET['url']) ? $_GET['url'] : 'home';
$url = rtrim($url, '/');
$urlParts = explode('/', $url);

$controllerName = ucfirst($urlParts[0]) . 'Controller';
$methodName = isset($urlParts[1]) ? $urlParts[1] : 'index';

// For now, just output what the router caught to verify it works
echo "MVC Router initialized.<br>";
echo "Requested Controller: " . htmlspecialchars($controllerName) . "<br>";
echo "Requested Method: " . htmlspecialchars($methodName) . "<br>";

?>
