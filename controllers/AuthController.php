<?php
// controllers/AuthController.php

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_uuid'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit();
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = "Por favor, complete todos los campos.";
            } else {
                $user = $this->userModel->getByUsername($username);
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Password matches, set session
                    $_SESSION['user_uuid'] = $user['uuid'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nombre_completo'] = $user['nombre_completo'];
                    $_SESSION['rol'] = $user['rol'];

                    header('Location: index.php?controller=dashboard&action=index');
                    exit();
                } else {
                    $error = "Usuario o contraseña incorrectos.";
                }
            }
        }

        // Include login view
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function register() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_uuid'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit();
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $nombre = trim($_POST['nombre_completo'] ?? '');

            if (empty($username) || empty($password) || empty($nombre)) {
                $error = "Por favor, complete todos los campos.";
            } elseif (strlen($password) < 6) {
                $error = "La contraseña debe tener al menos 6 caracteres.";
            } else {
                $existing = $this->userModel->getByUsername($username);
                if ($existing) {
                    $error = "El nombre de usuario ya se encuentra registrado.";
                } else {
                    if ($this->userModel->create($username, $password, $nombre, 'Administrador')) {
                        header('Location: index.php?controller=auth&action=login&success=registered');
                        exit();
                    } else {
                        $error = "Ocurrió un error al registrar la cuenta en la base de datos.";
                    }
                }
            }
        }

        // Include register view
        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        header('Location: index.php?controller=auth&action=login');
        exit();
    }

    public static function checkAuth() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_uuid'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
    }

    public static function getCurrentUser() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_uuid'])) {
            return [
                'uuid' => $_SESSION['user_uuid'],
                'username' => $_SESSION['username'],
                'nombre_completo' => $_SESSION['nombre_completo'],
                'rol' => $_SESSION['rol']
            ];
        }
        return null;
    }
}
