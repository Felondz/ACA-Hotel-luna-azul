<?php
// controllers/DashboardController.php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../models/Reservation.php';

class DashboardController {
    private $db;

    public function __construct() {
        AuthController::checkAuth();
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        $today = date('Y-m-d');

        // Statistics
        // 1. Total Guests
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM guests");
        $stmt->execute();
        $totalGuests = $stmt->fetchColumn();

        // 2. Active Reservations
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE estado = 'Confirmada'");
        $stmt->execute();
        $activeReservations = $stmt->fetchColumn();

        // 3. Occupied Rooms Today
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT numero_habitacion) FROM reservations 
                                    WHERE estado = 'Confirmada' AND fecha_ingreso <= :today_in AND fecha_salida >= :today_out");
        $stmt->bindParam(':today_in', $today);
        $stmt->bindParam(':today_out', $today);
        $stmt->execute();
        $occupiedRoomsToday = $stmt->fetchColumn();

        // 4. Total Rooms
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rooms");
        $stmt->execute();
        $totalRooms = $stmt->fetchColumn();

        // 5. Occupancy rate
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRoomsToday / $totalRooms) * 100, 1) : 0;

        // Recent Reservations
        $stmt = $this->db->prepare("SELECT r.*, g.nombre_completo AS guest_name 
                                    FROM reservations r 
                                    JOIN guests g ON r.guest_uuid = g.uuid 
                                    ORDER BY r.fecha_creacion DESC LIMIT 5");
        $stmt->execute();
        $recentReservations = $stmt->fetchAll();

        // Python report content (if previously generated)
        $pythonReport = null;
        if (isset($_SESSION['python_report'])) {
            $pythonReport = $_SESSION['python_report'];
            unset($_SESSION['python_report']); // consume it
        }

        require_once __DIR__ . '/../views/dashboard.php';
    }

    public function generateReport() {
        // Fetch all reservation data
        $stmt = $this->db->prepare("SELECT r.uuid, r.fecha_ingreso, r.fecha_salida, r.numero_habitacion, 
                                           r.numero_huespedes, r.estado, rm.tipo_habitacion, g.edad
                                    FROM reservations r
                                    JOIN rooms rm ON r.numero_habitacion = rm.numero_habitacion
                                    JOIN guests g ON r.guest_uuid = g.uuid");
        $stmt->execute();
        $reservations = $stmt->fetchAll();

        // Create temporary file path
        $tempDir = sys_get_temp_dir();
        $tempFile = $tempDir . '/luna_azul_data_' . uniqid() . '.json';
        
        // Write JSON data
        file_put_contents($tempFile, json_encode($reservations));

        // Locate Python report script
        $pythonScript = __DIR__ . '/../scripts/report.py';
        
        // Command injection prevention: $tempFile is built using sys_get_temp_dir() and uniqid(), but we still escape it.
        $escapedScript = escapeshellarg($pythonScript);
        $escapedFile = escapeshellarg($tempFile);
        
        // Run python3 script
        $command = "python3 {$escapedScript} {$escapedFile} 2>&1";
        $output = shell_exec($command);

        // Delete temporary file
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($output === null || empty(trim($output))) {
            $_SESSION['python_report'] = [
                'success' => false,
                'message' => "Error al ejecutar el script de análisis en Python. Verifique que Python 3 esté instalado."
            ];
        } else {
            $_SESSION['python_report'] = [
                'success' => true,
                'content' => $output
            ];
        }

        header('Location: index.php?controller=dashboard&action=index');
        exit();
    }
}
