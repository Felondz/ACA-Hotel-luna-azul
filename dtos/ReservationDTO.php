<?php
// dtos/ReservationDTO.php

class ReservationDTO {
    public $uuid;
    public $guestUuid;
    public $guestName; // Optional, useful for views
    public $numeroHabitacion;
    public $tipoHabitacion; // Optional, useful for views
    public $fechaIngreso;
    public $fechaSalida;
    public $numeroHuespedes;
    public $estado;

    public function __construct(
        $uuid,
        $guestUuid,
        $guestName,
        $numeroHabitacion,
        $tipoHabitacion,
        $fechaIngreso,
        $fechaSalida,
        $numeroHuespedes,
        $estado
    ) {
        $this->uuid = $uuid;
        $this->guestUuid = $guestUuid;
        $this->guestName = $guestName;
        $this->numeroHabitacion = $numeroHabitacion;
        $this->tipoHabitacion = $tipoHabitacion;
        $this->fechaIngreso = $fechaIngreso;
        $this->fechaSalida = $fechaSalida;
        $this->numeroHuespedes = (int)$numeroHuespedes;
        $this->estado = $estado;
    }

    public static function fromArray(array $data) {
        return new self(
            $data['uuid'] ?? null,
            $data['guest_uuid'] ?? $data['guestUuid'] ?? '',
            $data['guest_name'] ?? $data['guestName'] ?? '',
            $data['numero_habitacion'] ?? $data['numeroHabitacion'] ?? '',
            $data['tipo_habitacion'] ?? $data['tipoHabitacion'] ?? '',
            $data['fecha_ingreso'] ?? $data['fechaIngreso'] ?? '',
            $data['fecha_salida'] ?? $data['fechaSalida'] ?? '',
            $data['numero_huespedes'] ?? $data['numeroHuespedes'] ?? 1,
            $data['estado'] ?? 'Confirmada'
        );
    }

    public function toArray() {
        return [
            'uuid' => $this->uuid,
            'guest_uuid' => $this->guestUuid,
            'guest_name' => $this->guestName,
            'numero_habitacion' => $this->numeroHabitacion,
            'tipo_habitacion' => $this->tipoHabitacion,
            'fecha_ingreso' => $this->fechaIngreso,
            'fecha_salida' => $this->fechaSalida,
            'numero_huespedes' => $this->numeroHuespedes,
            'estado' => $this->estado
        ];
    }
}
