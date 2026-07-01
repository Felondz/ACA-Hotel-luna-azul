<?php
// views/layouts/header.php
$currentUser = AuthController::getCurrentUser();
$activeController = $_GET['controller'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Luna Azul - Dashboard</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="app-container">
    
    <!-- Sidebar Navigation -->
    <aside class="app-sidebar">
      <div class="sidebar-header">
        <div class="logo-icon">
          <i class="fa-solid fa-moon"></i>
        </div>
        <div class="logo-text">
          <h2>Luna Azul</h2>
          <span>Hotel Management</span>
        </div>
      </div>
      
      <nav class="sidebar-menu">
        <ul>
          <li>
            <a href="index.php?controller=dashboard&action=index" class="<?php echo $activeController === 'dashboard' ? 'active' : ''; ?>">
              <i class="fa-solid fa-chart-pie"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li>
            <a href="index.php?controller=guests&action=index" class="<?php echo $activeController === 'guests' ? 'active' : ''; ?>">
              <i class="fa-solid fa-users"></i>
              <span>Huéspedes</span>
            </a>
          </li>
          <li>
            <a href="index.php?controller=reservations&action=index" class="<?php echo $activeController === 'reservations' ? 'active' : ''; ?>">
              <i class="fa-solid fa-calendar-check"></i>
              <span>Reservas</span>
            </a>
          </li>
        </ul>
      </nav>
      
      <div class="sidebar-footer">
        <?php if ($currentUser): ?>
          <div class="user-profile">
            <div class="user-avatar">
              <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="user-info">
              <span class="user-name"><?php echo htmlspecialchars($currentUser['nombre_completo']); ?></span>
              <span class="user-role"><?php echo htmlspecialchars($currentUser['rol']); ?></span>
            </div>
          </div>
        <?php endif; ?>
        
        <a href="index.php?controller=auth&action=logout" class="btn-logout" title="Cerrar Sesión">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span>Cerrar Sesión</span>
        </a>
      </div>
    </aside>
    
    <!-- Main Content Container -->
    <main class="app-content">
      <header class="content-header">
        <h1 class="page-title">
          <?php
          switch ($activeController) {
              case 'guests':
                  echo '<i class="fa-solid fa-users icon-spacer"></i> Gestión de Huéspedes';
                  break;
              case 'reservations':
                  echo '<i class="fa-solid fa-calendar-check icon-spacer"></i> Control de Reservas';
                  break;
              case 'dashboard':
              default:
                  echo '<i class="fa-solid fa-chart-pie icon-spacer"></i> Vista General';
                  break;
          }
          ?>
        </h1>
        <div class="system-time">
          <i class="fa-regular fa-clock"></i>
          <span id="live-clock"><?php echo date('d/m/Y H:i'); ?></span>
        </div>
      </header>
      
      <div class="content-body">
