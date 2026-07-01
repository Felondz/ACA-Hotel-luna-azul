<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Cuenta - Hotel Luna Azul</title>
  
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
          <i class="fa-solid fa-user-plus"></i>
        </div>
        <h1>Crear Cuenta</h1>
        <p>Registrar nuevo usuario administrador</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form action="index.php?controller=auth&action=register" method="POST" id="register-form" class="login-form">
        <div class="form-group">
          <label for="nombre_completo">Nombre Completo</label>
          <div class="input-icon-wrapper">
            <i class="fa-solid fa-signature"></i>
            <input 
              type="text" 
              id="nombre_completo" 
              name="nombre_completo" 
              placeholder="Ej. Carlos Gomez" 
              value="<?php echo htmlspecialchars($_POST['nombre_completo'] ?? ''); ?>"
              required
            >
          </div>
        </div>

        <div class="form-group">
          <label for="username">Usuario</label>
          <div class="input-icon-wrapper">
            <i class="fa-solid fa-user"></i>
            <input 
              type="text" 
              id="username" 
              name="username" 
              placeholder="Elija un nombre de usuario" 
              value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
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
              placeholder="Mínimo 6 caracteres" 
              required
              autocomplete="new-password"
            >
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
          <span>Crear Cuenta</span>
          <i class="fa-solid fa-user-plus"></i>
        </button>
      </form>
      
      <div class="login-footer">
        <p>¿Ya tiene una cuenta? <a href="index.php?controller=auth&action=login" style="color: var(--primary-color); font-weight: 600;">Inicie sesión aquí</a></p>
      </div>
    </div>
  </div>

  <!-- Validation Javascript -->
  <script src="js/main.js"></script>
</body>
</html>
