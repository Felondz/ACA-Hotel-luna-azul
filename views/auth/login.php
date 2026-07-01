<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión - Hotel Luna Azul</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
  

  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <div class="login-logo">
          <i class="fa-solid fa-moon"></i>
        </div>
        <h1>Hotel Luna Azul</h1>
        <p>Sistema de Gestión de Huéspedes y Reservas</p>
      </div>

      <?php if (($_GET['success'] ?? '') === 'registered'): ?>
        <div class="alert alert-success">
          <i class="fa-solid fa-circle-check"></i>
          <span>¡Cuenta creada con éxito! Ya puede iniciar sesión.</span>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form action="index.php?controller=auth&action=login" method="POST" id="login-form" class="login-form">
        <div class="form-group">
          <label for="username">Usuario</label>
          <div class="input-icon-wrapper">
            <i class="fa-solid fa-user"></i>
            <input 
              type="text" 
              id="username" 
              name="username" 
              placeholder="Ingrese su usuario" 
              value="<?php echo htmlspecialchars($username ?? ''); ?>"
              required
              autocomplete="username"
            >
          </div>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <div class="input-icon-wrapper">
            <i class="fa-solid fa-lock"></i>
            <input 
              type="password" 
              id="password" 
              name="password" 
              placeholder="Ingrese su contraseña" 
              required
              autocomplete="current-password"
            >
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
          <span>Ingresar</span>
          <i class="fa-solid fa-arrow-right-to-bracket"></i>
        </button>
      </form>
      
      <div class="login-footer">
        <p style="margin-bottom: 8px;">¿No tiene cuenta? <a href="index.php?controller=auth&action=register" style="color: var(--primary-color); font-weight: 600;">Regístrese aquí</a></p>
        <span>Credenciales de prueba: admin / admin123</span>
      </div>
    </div>
  </div>

  <!-- Validation Javascript -->
  <script src="js/main.js"></script>
</body>
</html>
