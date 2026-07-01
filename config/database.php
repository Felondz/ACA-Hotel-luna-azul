<?php
// config/database.php

class Database
{
    private static $instance = null;
    private $conn;

    private $host = '127.0.0.1';
    private $db_name = 'hotel_luna_azul';
    private $username = 'root';
    private $password = 'LunaAzul2026#'; // Default for most local XAMPP/WampServer/MAMP environments

    private function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            die("Database connection error: " . $exception->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
