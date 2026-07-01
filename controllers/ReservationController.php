<?php
// controllers/ReservationController.php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../dtos/ReservationDTO.php';

class ReservationController {
    private $reservationModel;
    private $guestModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->reservationModel = new Reservation();
        $this->guestModel = new Guest();
    }

    public function index() {
        $reservations = $this->reservationModel->getAll();
        require_once __DIR__ . '/../views/reservations/index.php';
    }

    public function create() {
        $errors = [];
        $data = [
            'fecha_ingreso' => date('Y-m-d'),
            'fecha_salida' => date('Y-m-d', strtotime('+1 day')),
            'numero_huespedes' => 1
        ];
        
        $guests = $this->guestModel->getAll();
        $availableRooms = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $errors = $this->validate($data);

            // Double check guest exists
            if (empty($errors['guest_uuid'])) {
                $guest = $this->guestModel->getByUuid($data['guest_uuid']);
                if (!$guest) {
                    $errors['guest_uuid'] = "El huésped seleccionado no es válido.";
                }
            }

            if (empty($errors)) {
                try {
                    $reservationDto = ReservationDTO::fromArray($data);
                    $reservationDto->estado = 'Confirmada';
                    
                    if ($this->reservationModel->create($reservationDto)) {
                        header('Location: index.php?controller=reservations&action=index&success=created');
                        exit();
                    } else {
                        $errors['db'] = "Error al crear la reserva.";
                    }
                } catch (Exception $e) {
                    $errors['room'] = $e->getMessage();
                }
            }
        }

        // Fetch available rooms if dates are set
        if (!empty($data['fecha_ingreso']) && !empty($data['fecha_salida']) && empty($errors['fecha_ingreso']) && empty($errors['fecha_salida'])) {
            $availableRooms = $this->reservationModel->getAvailableRooms($data['fecha_ingreso'], $data['fecha_salida']);
        } else {
            $availableRooms = $this->reservationModel->getAllRooms();
        }

        require_once __DIR__ . '/../views/reservations/create.php';
    }

    public function edit() {
        $uuid = $_GET['uuid'] ?? '';
        if (empty($uuid)) {
            header('Location: index.php?controller=reservations&action=index');
            exit();
        }

        $reservation = $this->reservationModel->getByUuid($uuid);
        if (!$reservation) {
            header('Location: index.php?controller=reservations&action=index&error=notfound');
            exit();
        }

        $errors = [];
        $data = $reservation->toArray();
        
        $guests = $this->guestModel->getAll();
        $selectedGuest = $this->guestModel->getByUuid($reservation->guestUuid);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['uuid'] = $uuid; // Keep original UUID
            $errors = $this->validate($data);

            // Double check guest exists
            if (empty($errors['guest_uuid'])) {
                $guest = $this->guestModel->getByUuid($data['guest_uuid']);
                if (!$guest) {
                    $errors['guest_uuid'] = "El huésped seleccionado no es válido.";
                }
            }

            if (empty($errors)) {
                try {
                    $reservationDto = ReservationDTO::fromArray($data);
                    if ($this->reservationModel->update($reservationDto)) {
                        header('Location: index.php?controller=reservations&action=index&success=updated');
                        exit();
                    } else {
                        $errors['db'] = "Error al actualizar la reserva.";
                    }
                } catch (Exception $e) {
                    $errors['room'] = $e->getMessage();
                }
            }
        }

        // Fetch available rooms for edited booking (excluding self)
        if (!empty($data['fecha_ingreso']) && !empty($data['fecha_salida']) && empty($errors['fecha_ingreso']) && empty($errors['fecha_salida'])) {
            $availableRooms = $this->reservationModel->getAvailableRooms($data['fecha_ingreso'], $data['fecha_salida'], $uuid);
            // Ensure the current room is included in the list, even if it is technically reserved by this booking
            $currentRoomAlreadyInList = false;
            foreach ($availableRooms as $r) {
                if ($r['numero_habitacion'] == $reservation->numeroHabitacion) {
                    $currentRoomAlreadyInList = true;
                    break;
                }
            }
            if (!$currentRoomAlreadyInList) {
                $currentRoomDetails = $this->reservationModel->getRoomDetails($reservation->numeroHabitacion);
                if ($currentRoomDetails) {
                    $availableRooms[] = $currentRoomDetails;
                }
            }
        } else {
            $availableRooms = $this->reservationModel->getAllRooms();
        }

        require_once __DIR__ . '/../views/reservations/edit.php';
    }

    public function cancel() {
        $uuid = $_GET['uuid'] ?? '';
        if (!empty($uuid)) {
            if ($this->reservationModel->cancel($uuid)) {
                header('Location: index.php?controller=reservations&action=index&success=cancelled');
                exit();
            } else {
                header('Location: index.php?controller=reservations&action=index&error=cancel_failed');
                exit();
            }
        }
        header('Location: index.php?controller=reservations&action=index');
        exit();
    }

    public function getAvailableRoomsAjax() {
        header('Content-Type: application/json');
        
        $checkIn = $_GET['check_in'] ?? '';
        $checkOut = $_GET['check_out'] ?? '';
        $excludeUuid = $_GET['exclude_uuid'] ?? null;

        if (empty($checkIn) || empty($checkOut)) {
            echo json_encode([]);
            exit();
        }

        // Simple validation
        if (strtotime($checkIn) >= strtotime($checkOut)) {
            echo json_encode([]);
            exit();
        }

        $rooms = $this->reservationModel->getAvailableRooms($checkIn, $checkOut, $excludeUuid);
        
        // If editing, also ensure current room of this reservation is appended if missing
        if ($excludeUuid) {
            $res = $this->reservationModel->getByUuid($excludeUuid);
            if ($res) {
                $hasCurrentRoom = false;
                foreach ($rooms as $r) {
                    if ($r['numero_habitacion'] === $res->numeroHabitacion) {
                        $hasCurrentRoom = true;
                        break;
                    }
                }
                if (!$hasCurrentRoom) {
                    $roomDetails = $this->reservationModel->getRoomDetails($res->numeroHabitacion);
                    if ($roomDetails) {
                        $rooms[] = $roomDetails;
                    }
                }
            }
        }

        echo json_encode($rooms);
        exit();
    }

    private function validate($data) {
        $errors = [];

        if (empty(trim($data['guest_uuid'] ?? ''))) {
            $errors['guest_uuid'] = "Debe seleccionar un huésped.";
        }

        if (empty(trim($data['numero_habitacion'] ?? ''))) {
            $errors['numero_habitacion'] = "Debe seleccionar una habitación.";
        }

        $checkIn = $data['fecha_ingreso'] ?? '';
        $checkOut = $data['fecha_salida'] ?? '';

        if (empty($checkIn)) {
            $errors['fecha_ingreso'] = "La fecha de ingreso es requerida.";
        }

        if (empty($checkOut)) {
            $errors['fecha_salida'] = "La fecha de salida es requerida.";
        }

        if (!empty($checkIn) && !empty($checkOut)) {
            $timeIn = strtotime($checkIn);
            $timeOut = strtotime($checkOut);
            $today = strtotime(date('Y-m-d'));

            if ($timeIn >= $timeOut) {
                $errors['fecha_salida'] = "La fecha de salida debe ser posterior a la fecha de ingreso.";
            }

            // Only check if it is a new reservation
            if (empty($data['uuid'])) {
                if ($timeIn < $today) {
                    $errors['fecha_ingreso'] = "La fecha de ingreso no puede ser anterior a la fecha de hoy.";
                }
            }
        }

        $numGuests = intval($data['numero_huespedes'] ?? 0);
        if ($numGuests <= 0) {
            $errors['numero_huespedes'] = "El número de huéspedes debe ser mayor a 0.";
        } else if (!empty($data['numero_habitacion'])) {
            $room = $this->reservationModel->getRoomDetails($data['numero_habitacion']);
            if ($room) {
                $tipo = $room['tipo_habitacion'];
                $capacidadMax = 1;
                if ($tipo === 'Sencilla') $capacidadMax = 1;
                elseif ($tipo === 'Doble') $capacidadMax = 2;
                elseif ($tipo === 'Suite') $capacidadMax = 4;
                elseif ($tipo === 'Familiar') $capacidadMax = 6;
                
                if ($numGuests > $capacidadMax) {
                    $errors['numero_huespedes'] = "La habitación " . $room['numero_habitacion'] . " (" . $tipo . ") solo permite alojar hasta " . $capacidadMax . " persona(s).";
                }
            }
        }

        return $errors;
    }
}
