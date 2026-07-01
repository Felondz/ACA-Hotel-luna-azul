<?php
// models/Reservation.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../dtos/ReservationDTO.php';

class Reservation {
    private $db;
    private $table = 'reservations';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $query = "SELECT r.*, g.nombre_completo AS guest_name, rm.tipo_habitacion 
                  FROM " . $this->table . " r
                  JOIN guests g ON r.guest_uuid = g.uuid
                  JOIN rooms rm ON r.numero_habitacion = rm.numero_habitacion
                  ORDER BY r.fecha_ingreso DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $reservations = [];
        while ($row = $stmt->fetch()) {
            $reservations[] = ReservationDTO::fromArray($row);
        }
        return $reservations;
    }

    public function getByUuid($uuid) {
        $query = "SELECT r.*, g.nombre_completo AS guest_name, rm.tipo_habitacion 
                  FROM " . $this->table . " r
                  JOIN guests g ON r.guest_uuid = g.uuid
                  JOIN rooms rm ON r.numero_habitacion = rm.numero_habitacion
                  WHERE r.uuid = :uuid LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
        $stmt->execute();
        
        $row = $stmt->fetch();
        return $row ? ReservationDTO::fromArray($row) : null;
    }

    public function isRoomAvailable($roomNumber, $checkIn, $checkOut, $excludeReservationUuid = null) {
        // Enforce: check-out at 10 AM, check-in at 12 PM.
        // Overlap query: In1 < Out2 AND Out1 > In2
        // Existing booking overlaps with requested checkIn/checkOut if:
        // existing.fecha_ingreso < requested.checkOut AND existing.fecha_salida > requested.checkIn
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE numero_habitacion = :room_number 
                  AND estado = 'Confirmada'
                  AND fecha_ingreso < :checkout 
                  AND fecha_salida > :checkin";
        
        if ($excludeReservationUuid) {
            $query .= " AND uuid != :exclude_uuid";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':room_number', $roomNumber);
        $stmt->bindParam(':checkin', $checkIn);
        $stmt->bindParam(':checkout', $checkOut);
        
        if ($excludeReservationUuid) {
            $stmt->bindParam(':exclude_uuid', $excludeReservationUuid);
        }

        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        return $count == 0;
    }

    public function getAvailableRooms($checkIn, $checkOut, $excludeReservationUuid = null) {
        // Find all rooms not reserved during this period
        $query = "SELECT * FROM rooms rm 
                  WHERE rm.numero_habitacion NOT IN (
                      SELECT r.numero_habitacion FROM " . $this->table . " r
                      WHERE r.estado = 'Confirmada'
                      AND r.fecha_ingreso < :checkout 
                      AND r.fecha_salida > :checkin";
        
        if ($excludeReservationUuid) {
            $query .= " AND r.uuid != :exclude_uuid";
        }
        
        $query .= " ) ORDER BY rm.numero_habitacion ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':checkin', $checkIn);
        $stmt->bindParam(':checkout', $checkOut);

        if ($excludeReservationUuid) {
            $stmt->bindParam(':exclude_uuid', $excludeReservationUuid);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRoomDetails($roomNumber) {
        $query = "SELECT * FROM rooms WHERE numero_habitacion = :room_number LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':room_number', $roomNumber);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAllRooms() {
        $query = "SELECT * FROM rooms ORDER BY numero_habitacion ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(ReservationDTO $reservationDto) {
        // Enforce room availability verification first
        if (!$this->isRoomAvailable($reservationDto->numeroHabitacion, $reservationDto->fechaIngreso, $reservationDto->fechaSalida)) {
            throw new Exception("La habitación " . $reservationDto->numeroHabitacion . " ya se encuentra ocupada en las fechas seleccionadas.");
        }

        $uuid = $reservationDto->uuid ?: $this->generateUuid();
        
        $query = "INSERT INTO " . $this->table . " 
                  (uuid, guest_uuid, numero_habitacion, fecha_ingreso, fecha_salida, numero_huespedes, estado) 
                  VALUES (:uuid, :guest_uuid, :numero_habitacion, :fecha_ingreso, :fecha_salida, :numero_huespedes, :estado)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
        $stmt->bindParam(':guest_uuid', $reservationDto->guestUuid);
        $stmt->bindParam(':numero_habitacion', $reservationDto->numeroHabitacion);
        $stmt->bindParam(':fecha_ingreso', $reservationDto->fechaIngreso);
        $stmt->bindParam(':fecha_salida', $reservationDto->fechaSalida);
        $stmt->bindParam(':numero_huespedes', $reservationDto->numeroHuespedes, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $reservationDto->estado);

        if ($stmt->execute()) {
            $reservationDto->uuid = $uuid;
            return true;
        }
        return false;
    }

    public function update(ReservationDTO $reservationDto) {
        // Enforce room availability verification first, excluding current reservation
        if (!$this->isRoomAvailable($reservationDto->numeroHabitacion, $reservationDto->fechaIngreso, $reservationDto->fechaSalida, $reservationDto->uuid)) {
            throw new Exception("La habitación " . $reservationDto->numeroHabitacion . " ya se encuentra ocupada en las fechas seleccionadas.");
        }

        $query = "UPDATE " . $this->table . " SET 
                  guest_uuid = :guest_uuid, 
                  numero_habitacion = :numero_habitacion, 
                  fecha_ingreso = :fecha_ingreso, 
                  fecha_salida = :fecha_salida, 
                  numero_huespedes = :numero_huespedes, 
                  estado = :estado 
                  WHERE uuid = :uuid";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $reservationDto->uuid);
        $stmt->bindParam(':guest_uuid', $reservationDto->guestUuid);
        $stmt->bindParam(':numero_habitacion', $reservationDto->numeroHabitacion);
        $stmt->bindParam(':fecha_ingreso', $reservationDto->fechaIngreso);
        $stmt->bindParam(':fecha_salida', $reservationDto->fechaSalida);
        $stmt->bindParam(':numero_huespedes', $reservationDto->numeroHuespedes, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $reservationDto->estado);

        return $stmt->execute();
    }

    public function cancel($uuid) {
        $query = "UPDATE " . $this->table . " SET estado = 'Cancelada' WHERE uuid = :uuid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
        return $stmt->execute();
    }

    public function delete($uuid) {
        $query = "DELETE FROM " . $this->table . " WHERE uuid = :uuid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
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
