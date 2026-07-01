<?php
// models/User.php

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create($username, $password, $nombreCompleto, $rol = 'Administrador') {
        $uuid = $this->generateUuid();
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO " . $this->table . " (uuid, username, password_hash, nombre_completo, rol) 
                  VALUES (:uuid, :username, :password_hash, :nombre_completo, :rol)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':nombre_completo', $nombreCompleto);
        $stmt->bindParam(':rol', $rol);

        return $stmt->execute();
    }

    private function generateUuid() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
