<?php
// public/index.php

// Show errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Autoload / Include required controllers and models
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/GuestController.php';
require_once __DIR__ . '/../controllers/ReservationController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

// Get routing parameters
$controllerName = $_GET['controller'] ?? 'dashboard';
$actionName = $_GET['action'] ?? 'index';

// Authentication Guard
// Allow auth controller actions to be called without logging in
if ($controllerName !== 'auth') {
    AuthController::checkAuth();
}

// Route mapping
switch ($controllerName) {
    case 'auth':
        $controller = new AuthController();
        break;
    case 'guests':
        $controller = new GuestController();
        break;
    case 'reservations':
        $controller = new ReservationController();
        break;
    case 'dashboard':
    default:
        $controller = new DashboardController();
        $controllerName = 'dashboard'; // Normalize
        break;
}

// Check if action method exists on controller
if (method_exists($controller, $actionName)) {
    $controller->$actionName();
} else {
    // If action doesn't exist, show error or fall back to index
    if (method_exists($controller, 'index')) {
        $controller->index();
    } else {
        http_response_code(404);
        echo "404 - Acción no encontrada";
    }
}
