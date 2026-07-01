<?php
// public/setup_admin.php
// Diagnostic and Seeding Helper Script for Hotel Luna Azul

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

echo "<h2>Hotel Luna Azul - Diagnostics & Administrator Setup</h2>";

try {
    echo "[+] Intentando conectar a la base de datos...<br>";
    $db = Database::getInstance()->getConnection();
    echo "[✔] ¡Conexión exitosa a la base de datos MySQL!<br><br>";

    // Verify or Create users table
    echo "[+] Verificando existencia de la tabla 'users'...<br>";
    $query = "CREATE TABLE IF NOT EXISTS `users` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `uuid` VARCHAR(36) UNIQUE NOT NULL,
      `username` VARCHAR(50) UNIQUE NOT NULL,
      `password_hash` VARCHAR(255) NOT NULL,
      `nombre_completo` VARCHAR(100) NOT NULL,
      `rol` VARCHAR(20) DEFAULT 'Administrador',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $db->exec($query);
    echo "[✔] Tabla 'users' verificada/creada con éxito.<br><br>";

    // Generate fresh hash for admin123
    $username = 'admin';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $nombre = 'Administrador General';
    $rol = 'Administrador';
    $uuid = 'e8b0dfcb-7c98-4c4f-9e7b-c0ee2d1a3be9';

    echo "[+] Sembrando usuario administrador 'admin'...<br>";
    
    // Check if user already exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        // Update password hash to make absolutely sure it is admin123
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash, nombre_completo = :nombre, rol = :rol WHERE username = :username");
        $stmt->execute([
            ':hash' => $hash,
            ':nombre' => $nombre,
            ':rol' => $rol,
            ':username' => $username
        ]);
        echo "[✔] Usuario 'admin' ya existía. Contraseña restablecida a: <b>admin123</b><br>";
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO users (uuid, username, password_hash, nombre_completo, rol) VALUES (:uuid, :username, :hash, :nombre, :rol)");
        $stmt->execute([
            ':uuid' => $uuid,
            ':username' => $username,
            ':hash' => $hash,
            ':nombre' => $nombre,
            ':rol' => $rol
        ]);
        echo "[✔] Usuario 'admin' creado exitosamente con contraseña: <b>admin123</b><br>";
    }

    echo "<br>[🎉] <b>Proceso completado. Intente iniciar sesión en la aplicación.</b><br>";
    echo "<a href='index.php?controller=auth&action=login'>Ir al Login</a>";

} catch (PDOException $e) {
    echo "<br><span style='color:red;'>[❌] ERROR DE CONEXIÓN:</span> " . $e->getMessage() . "<br>";
    echo "<b>Verifique que:</b><br>";
    echo "1. El servidor MySQL esté encendido (`sudo systemctl start mysqld`).<br>";
    echo "2. Las credenciales en <b>config/database.php</b> sean correctas.<br>";
    echo "3. La base de datos <b>hotel_luna_azul</b> haya sido creada en MySQL (corra: `mysql -u root -p < database.sql` en su consola).<br>";
}
