<?php
// controllers/GuestController.php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../dtos/GuestDTO.php';

class GuestController {
    private $guestModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->guestModel = new Guest();
    }

    public function index() {
        $guests = $this->guestModel->getAll();
        require_once __DIR__ . '/../views/guests/index.php';
    }

    public function create() {
        $errors = [];
        $data = [];
        $isAjax = isset($_GET['ajax']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $errors = $this->validate($data);

            if (empty($errors)) {
                // Check if document already exists
                $existing = $this->guestModel->getByDocument($data['tipo_documento'], $data['numero_documento']);
                if ($existing) {
                    $errors['numero_documento'] = "Ya existe un huésped registrado con este documento.";
                } else {
                    $guestDto = GuestDTO::fromArray($data);
                    if ($this->guestModel->create($guestDto)) {
                        if ($isAjax) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'guest' => $guestDto->toArray()]);
                            exit();
                        }
                        header('Location: index.php?controller=guests&action=index&success=created');
                        exit();
                    } else {
                        $errors['db'] = "Error al guardar el huésped en la base de datos.";
                    }
                }
            }

            if ($isAjax && !empty($errors)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit();
            }
        }

        require_once __DIR__ . '/../views/guests/create.php';
    }

    public function edit() {
        $uuid = $_GET['uuid'] ?? '';
        if (empty($uuid)) {
            header('Location: index.php?controller=guests&action=index');
            exit();
        }

        $guest = $this->guestModel->getByUuid($uuid);
        if (!$guest) {
            header('Location: index.php?controller=guests&action=index&error=notfound');
            exit();
        }

        $errors = [];
        $data = $guest->toArray();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['uuid'] = $uuid; // Keep original UUID
            $errors = $this->validate($data);

            if (empty($errors)) {
                // Check document uniqueness (excluding self)
                $existing = $this->guestModel->getByDocument($data['tipo_documento'], $data['numero_documento']);
                if ($existing && $existing->uuid !== $uuid) {
                    $errors['numero_documento'] = "Ya existe otro huésped registrado con este documento.";
                } else {
                    $guestDto = GuestDTO::fromArray($data);
                    if ($this->guestModel->update($guestDto)) {
                        header('Location: index.php?controller=guests&action=index&success=updated');
                        exit();
                    } else {
                        $errors['db'] = "Error al actualizar el huésped.";
                    }
                }
            }
        }

        require_once __DIR__ . '/../views/guests/edit.php';
    }

    public function delete() {
        $uuid = $_GET['uuid'] ?? '';
        if (!empty($uuid)) {
            // Delete guest
            if ($this->guestModel->delete($uuid)) {
                header('Location: index.php?controller=guests&action=index&success=deleted');
                exit();
            } else {
                header('Location: index.php?controller=guests&action=index&error=delete_failed');
                exit();
            }
        }
        header('Location: index.php?controller=guests&action=index');
        exit();
    }

    public function search() {
        // Handle AJAX search
        header('Content-Type: application/json');
        $term = $_GET['term'] ?? '';
        if (strlen($term) < 2) {
            echo json_encode([]);
            exit();
        }

        $results = $this->guestModel->search($term);
        $data = array_map(function($guest) {
            return $guest->toArray();
        }, $results);

        echo json_encode($data);
        exit();
    }

    private function validate($data) {
        $errors = [];

        if (empty(trim($data['nombre_completo'] ?? ''))) {
            $errors['nombre_completo'] = "El nombre completo es requerido.";
        }

        if (empty(trim($data['tipo_documento'] ?? ''))) {
            $errors['tipo_documento'] = "El tipo de documento es requerido.";
        }

        if (empty(trim($data['numero_documento'] ?? ''))) {
            $errors['numero_documento'] = "El número de documento es requerido.";
        }

        if (empty(trim($data['direccion'] ?? ''))) {
            $errors['direccion'] = "La dirección es requerida.";
        }

        if (empty(trim($data['celular'] ?? ''))) {
            $errors['celular'] = "El celular es requerido.";
        }

        $edad = intval($data['edad'] ?? 0);
        if ($edad <= 0) {
            $errors['edad'] = "La edad debe ser un número entero positivo.";
        }

        $email = trim($data['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Ingrese un correo electrónico válido.";
        }

        if (empty(trim($data['contacto_emergencia'] ?? ''))) {
            $errors['contacto_emergencia'] = "El contacto de emergencia es requerido.";
        }

        if (empty(trim($data['parentesco_contacto'] ?? ''))) {
            $errors['parentesco_contacto'] = "El parentesco del contacto es requerido.";
        }

        return $errors;
    }
}
