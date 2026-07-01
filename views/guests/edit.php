<?php
// views/guests/edit.php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="glass-panel">
  <div class="panel-header">
    <h2><i class="fa-solid fa-user-pen header-icon"></i> Editar Datos de Huésped</h2>
    <a href="index.php?controller=guests&action=index" class="btn btn-secondary btn-sm">
      <i class="fa-solid fa-arrow-left"></i> Volver al Listado
    </a>
  </div>
  
  <div class="panel-body">
    <?php if (isset($errors['db'])): ?>
      <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?php echo htmlspecialchars($errors['db']); ?></span>
      </div>
    <?php endif; ?>

    <form action="index.php?controller=guests&action=edit&uuid=<?php echo htmlspecialchars($uuid); ?>" method="POST" id="guest-form" class="form-grid-layout" onsubmit="return validateGuestForm()">
      
      <!-- Section 1: Datos Personales -->
      <fieldset class="form-fieldset">
        <legend><i class="fa-solid fa-address-card"></i> Datos Personales</legend>
        
        <div class="form-row">
          <div class="form-group col-8">
            <label for="nombre_completo">Nombre Completo <span class="required">*</span></label>
            <input 
              type="text" 
              id="nombre_completo" 
              name="nombre_completo" 
              placeholder="Ej. Juan Carlos Pérez Gomez" 
              value="<?php echo htmlspecialchars($data['nombre_completo'] ?? ''); ?>"
              class="<?php echo isset($errors['nombre_completo']) ? 'input-error' : ''; ?>"
            >
            <?php if (isset($errors['nombre_completo'])): ?>
              <span class="error-text"><?php echo $errors['nombre_completo']; ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group col-4">
            <label for="edad">Edad <span class="required">*</span></label>
            <input 
              type="number" 
              id="edad" 
              name="edad" 
              min="1" 
              max="120"
              placeholder="Ej. 30" 
              value="<?php echo htmlspecialchars($data['edad'] ?? ''); ?>"
              class="<?php echo isset($errors['edad']) ? 'input-error' : ''; ?>"
            >
            <?php if (isset($errors['edad'])): ?>
              <span class="error-text"><?php echo $errors['edad']; ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-4">
            <label for="tipo_documento">Tipo de Documento <span class="required">*</span></label>
            <select 
              id="tipo_documento" 
              name="tipo_documento" 
              class="<?php echo isset($errors['tipo_documento']) ? 'input-error' : ''; ?>"
            >
              <option value="">Seleccione...</option>
              <option value="CC" <?php echo ($data['tipo_documento'] ?? '') === 'CC' ? 'selected' : ''; ?>>Cédula de Ciudadanía (CC)</option>
              <option value="TI" <?php echo ($data['tipo_documento'] ?? '') === 'TI' ? 'selected' : ''; ?>>Tarjeta de Identidad (TI)</option>
              <option value="CE" <?php echo ($data['tipo_documento'] ?? '') === 'CE' ? 'selected' : ''; ?>>Cédula de Extranjería (CE)</option>
              <option value="Pasaporte" <?php echo ($data['tipo_documento'] ?? '') === 'Pasaporte' ? 'selected' : ''; ?>>Pasaporte</option>
            </select>
            <?php if (isset($errors['tipo_documento'])): ?>
              <span class="error-text"><?php echo $errors['tipo_documento']; ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group col-8">
            <label for="numero_documento">Número de Documento <span class="required">*</span></label>
            <input 
              type="text" 
              id="numero_documento" 
              name="numero_documento" 
              placeholder="Ej. 10203040" 
              value="<?php echo htmlspecialchars($data['numero_documento'] ?? ''); ?>"
              class="<?php echo isset($errors['numero_documento']) ? 'input-error' : ''; ?>"
            >
            <?php if (isset($errors['numero_documento'])): ?>
              <span class="error-text"><?php echo $errors['numero_documento']; ?></span>
            <?php endif; ?>
          </div>
        </div>
      </fieldset>

      <!-- Section 2: Información de Contacto -->
      <fieldset class="form-fieldset">
        <legend><i class="fa-solid fa-circle-phone"></i> Información de Contacto y Ubicación</legend>
        
        <div class="form-row">
          <div class="form-group col-12">
            <label for="direccion">Dirección de Residencia <span class="required">*</span></label>
            <input 
              type="text" 
              id="direccion" 
              name="direccion" 
              placeholder="Ej. Calle 45 # 12-30, Apto 301" 
              value="<?php echo htmlspecialchars($data['direccion'] ?? ''); ?>"
              class="<?php echo isset($errors['direccion']) ? 'input-error' : ''; ?>"
              autocomplete="street-address"
            >
            <?php if (isset($errors['direccion'])): ?>
              <span class="error-text"><?php echo $errors['direccion']; ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-6">
            <label for="celular">Celular <span class="required">*</span></label>
            <input 
              type="tel" 
              id="celular" 
              name="celular" 
              placeholder="Ej. 3101234567" 
              value="<?php echo htmlspecialchars($data['celular'] ?? ''); ?>"
              class="<?php echo isset($errors['celular']) ? 'input-error' : ''; ?>"
              autocomplete="tel"
            >
            <?php if (isset($errors['celular'])): ?>
              <span class="error-text"><?php echo $errors['celular']; ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group col-6">
            <label for="telefono">Teléfono Fijo</label>
            <input 
              type="tel" 
              id="telefono" 
              name="telefono" 
              placeholder="Ej. 6012345678" 
              value="<?php echo htmlspecialchars($data['telefono'] ?? ''); ?>"
              autocomplete="tel"
            >
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-12">
            <label for="email">Correo Electrónico <span class="required">*</span></label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              placeholder="Ej. huesped@email.com" 
              value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
              class="<?php echo isset($errors['email']) ? 'input-error' : ''; ?>"
              autocomplete="email"
            >
            <?php if (isset($errors['email'])): ?>
              <span class="error-text"><?php echo $errors['email']; ?></span>
            <?php endif; ?>
          </div>
        </div>
      </fieldset>

      <!-- Section 3: Contacto de Emergencia -->
      <fieldset class="form-fieldset">
        <legend><i class="fa-solid fa-kit-medical"></i> Contacto de Emergencia</legend>
        
        <div class="form-row">
          <div class="form-group col-7">
            <label for="contacto_emergencia">Nombre Completo del Contacto <span class="required">*</span></label>
            <input 
              type="text" 
              id="contacto_emergencia" 
              name="contacto_emergencia" 
              placeholder="Ej. María Josefa Rodriguez" 
              value="<?php echo htmlspecialchars($data['contacto_emergencia'] ?? ''); ?>"
              class="<?php echo isset($errors['contacto_emergencia']) ? 'input-error' : ''; ?>"
            >
            <?php if (isset($errors['contacto_emergencia'])): ?>
              <span class="error-text"><?php echo $errors['contacto_emergencia']; ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group col-5">
            <label for="parentesco_contacto">Parentesco / Relación <span class="required">*</span></label>
            <select 
              id="parentesco_contacto" 
              name="parentesco_contacto" 
              class="<?php echo isset($errors['parentesco_contacto']) ? 'input-error' : ''; ?>"
            >
              <option value="">Seleccione...</option>
              <option value="Madre" <?php echo ($data['parentesco_contacto'] ?? '') === 'Madre' ? 'selected' : ''; ?>>Madre</option>
              <option value="Padre" <?php echo ($data['parentesco_contacto'] ?? '') === 'Padre' ? 'selected' : ''; ?>>Padre</option>
              <option value="Esposo(a)" <?php echo ($data['parentesco_contacto'] ?? '') === 'Esposo(a)' ? 'selected' : ''; ?>>Esposo(a)</option>
              <option value="Hijo(a)" <?php echo ($data['parentesco_contacto'] ?? '') === 'Hijo(a)' ? 'selected' : ''; ?>>Hijo(a)</option>
              <option value="Hermano(a)" <?php echo ($data['parentesco_contacto'] ?? '') === 'Hermano(a)' ? 'selected' : ''; ?>>Hermano(a)</option>
              <option value="Otro" <?php echo ($data['parentesco_contacto'] ?? '') === 'Otro' ? 'selected' : ''; ?>>Otro</option>
            </select>
            <?php if (isset($errors['parentesco_contacto'])): ?>
              <span class="error-text"><?php echo $errors['parentesco_contacto']; ?></span>
            <?php endif; ?>
          </div>
        </div>
      </fieldset>

      <!-- Submit Section -->
      <div class="form-actions-bar">
        <a href="index.php?controller=guests&action=index" class="btn btn-secondary">
          <i class="fa-solid fa-xmark"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
        </button>
      </div>

    </form>
  </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
