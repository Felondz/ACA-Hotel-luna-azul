<?php
// dtos/GuestDTO.php

class GuestDTO {
    public $uuid;
    public $nombreCompleto;
    public $tipoDocumento;
    public $numeroDocumento;
    public $direccion;
    public $telefono;
    public $celular;
    public $edad;
    public $email;
    public $contactoEmergencia;
    public $parentescoContacto;

    public function __construct(
        $uuid,
        $nombreCompleto,
        $tipoDocumento,
        $numeroDocumento,
        $direccion,
        $telefono,
        $celular,
        $edad,
        $email,
        $contactoEmergencia,
        $parentescoContacto
    ) {
        $this->uuid = $uuid;
        $this->nombreCompleto = $nombreCompleto;
        $this->tipoDocumento = $tipoDocumento;
        $this->numeroDocumento = $numeroDocumento;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
        $this->celular = $celular;
        $this->edad = (int)$edad;
        $this->email = $email;
        $this->contactoEmergencia = $contactoEmergencia;
        $this->parentescoContacto = $parentescoContacto;
    }

    public static function fromArray(array $data) {
        return new self(
            $data['uuid'] ?? null,
            $data['nombre_completo'] ?? $data['nombreCompleto'] ?? '',
            $data['tipo_documento'] ?? $data['tipoDocumento'] ?? '',
            $data['numero_documento'] ?? $data['numeroDocumento'] ?? '',
            $data['direccion'] ?? '',
            $data['telefono'] ?? null,
            $data['celular'] ?? '',
            $data['edad'] ?? 0,
            $data['email'] ?? '',
            $data['contacto_emergencia'] ?? $data['contactoEmergencia'] ?? '',
            $data['parentesco_contacto'] ?? $data['parentescoContacto'] ?? ''
        );
    }

    public function toArray() {
        return [
            'uuid' => $this->uuid,
            'nombre_completo' => $this->nombreCompleto,
            'tipo_documento' => $this->tipoDocumento,
            'numero_documento' => $this->numeroDocumento,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'celular' => $this->celular,
            'edad' => $this->edad,
            'email' => $this->email,
            'contacto_emergencia' => $this->contactoEmergencia,
            'parentesco_contacto' => $this->parentescoContacto
        ];
    }
}
