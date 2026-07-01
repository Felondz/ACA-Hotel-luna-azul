<?php
// models/Guest.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../dtos/GuestDTO.php';

class Guest {
    private $db;
    private $table = 'guests';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY nombre_completo ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $guests = [];
        while ($row = $stmt->fetch()) {
            $guests[] = GuestDTO::fromArray($row);
        }
        return $guests;
    }

    public function getByUuid($uuid) {
        $query = "SELECT * FROM " . $this->table . " WHERE uuid = :uuid LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
        $stmt->execute();
        
        $row = $stmt->fetch();
        return $row ? GuestDTO::fromArray($row) : null;
    }

    public function getByDocument($tipoDocumento, $numeroDocumento) {
        $query = "SELECT * FROM " . $this->table . " WHERE tipo_documento = :tipo_documento AND numero_documento = :numero_documento LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tipo_documento', $tipoDocumento);
        $stmt->bindParam(':numero_documento', $numeroDocumento);
        $stmt->execute();
        
        $row = $stmt->fetch();
        return $row ? GuestDTO::fromArray($row) : null;
    }

    public function create(GuestDTO $guestDto) {
        $uuid = $guestDto->uuid ?: $this->generateUuid();
        
        $query = "INSERT INTO " . $this->table . " 
                  (uuid, nombre_completo, tipo_documento, numero_documento, direccion, telefono, celular, edad, email, contacto_emergencia, parentesco_contacto) 
                  VALUES (:uuid, :nombre_completo, :tipo_documento, :numero_documento, :direccion, :telefono, :celular, :edad, :email, :contacto_emergencia, :parentesco_contacto)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
        $stmt->bindParam(':nombre_completo', $guestDto->nombreCompleto);
        $stmt->bindParam(':tipo_documento', $guestDto->tipoDocumento);
        $stmt->bindParam(':numero_documento', $guestDto->numeroDocumento);
        $stmt->bindParam(':direccion', $guestDto->direccion);
        $stmt->bindParam(':telefono', $guestDto->telefono);
        $stmt->bindParam(':celular', $guestDto->celular);
        $stmt->bindParam(':edad', $guestDto->edad, PDO::PARAM_INT);
        $stmt->bindParam(':email', $guestDto->email);
        $stmt->bindParam(':contacto_emergencia', $guestDto->contactoEmergencia);
        $stmt->bindParam(':parentesco_contacto', $guestDto->parentescoContacto);

        if ($stmt->execute()) {
            $guestDto->uuid = $uuid;
            return true;
        }
        return false;
    }

    public function update(GuestDTO $guestDto) {
        $query = "UPDATE " . $this->table . " SET 
                  nombre_completo = :nombre_completo, 
                  tipo_documento = :tipo_documento, 
                  numero_documento = :numero_documento, 
                  direccion = :direccion, 
                  telefono = :telefono, 
                  celular = :celular, 
                  edad = :edad, 
                  email = :email, 
                  contacto_emergencia = :contacto_emergencia, 
                  parentesco_contacto = :parentesco_contacto 
                  WHERE uuid = :uuid";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $guestDto->uuid);
        $stmt->bindParam(':nombre_completo', $guestDto->nombreCompleto);
        $stmt->bindParam(':tipo_documento', $guestDto->tipoDocumento);
        $stmt->bindParam(':numero_documento', $guestDto->numeroDocumento);
        $stmt->bindParam(':direccion', $guestDto->direccion);
        $stmt->bindParam(':telefono', $guestDto->telefono);
        $stmt->bindParam(':celular', $guestDto->celular);
        $stmt->bindParam(':edad', $guestDto->edad, PDO::PARAM_INT);
        $stmt->bindParam(':email', $guestDto->email);
        $stmt->bindParam(':contacto_emergencia', $guestDto->contactoEmergencia);
        $stmt->bindParam(':parentesco_contacto', $guestDto->parentescoContacto);

        return $stmt->execute();
    }

    public function delete($uuid) {
        $query = "DELETE FROM " . $this->table . " WHERE uuid = :uuid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
        return $stmt->execute();
    }

    public function search($term) {
        $termLike = '%' . $term . '%';
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE nombre_completo LIKE :term 
                  OR numero_documento LIKE :term 
                  ORDER BY nombre_completo ASC LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':term', $termLike);
        $stmt->execute();

        $guests = [];
        while ($row = $stmt->fetch()) {
            $guests[] = GuestDTO::fromArray($row);
        }
        return $guests;
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
