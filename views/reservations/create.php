<?php
// views/reservations/create.php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="glass-panel">
  <div class="panel-header">
    <h2><i class="fa-solid fa-calendar-plus header-icon"></i> Registrar Nueva Reserva</h2>
    <a href="index.php?controller=reservations&action=index" class="btn btn-secondary btn-sm">
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

    <?php if (isset($errors['room'])): ?>
      <div class="alert alert-danger">
        <i class="fa-solid fa-door-closed"></i>
        <span><?php echo htmlspecialchars($errors['room']); ?></span>
      </div>
    <?php endif; ?>

    <form action="index.php?controller=reservations&action=create" method="POST" id="reservation-form" class="form-grid-layout" onsubmit="return validateReservationForm()">
      
      <!-- Section 1: Fechas de Reserva -->
      <fieldset class="form-fieldset">
        <legend><i class="fa-solid fa-calendar-days"></i> Fechas de la Estadía</legend>
        
        <div class="form-row">
          <div class="form-group col-6">
            <label for="fecha_ingreso">Fecha de Ingreso (Check-In) <span class="required">*</span></label>
            <input 
              type="date" 
              id="fecha_ingreso" 
              name="fecha_ingreso" 
              min="<?php echo date('Y-m-d'); ?>"
              value="<?php echo htmlspecialchars($data['fecha_ingreso'] ?? ''); ?>"
              class="<?php echo isset($errors['fecha_ingreso']) ? 'input-error' : ''; ?>"
              onchange="fetchAvailableRooms()"
            >
            <span class="input-hint">El ingreso es a partir de las 12:00 PM</span>
            <?php if (isset($errors['fecha_ingreso'])): ?>
              <span class="error-text"><?php echo $errors['fecha_ingreso']; ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group col-6">
            <label for="fecha_salida">Fecha de Salida (Check-Out) <span class="required">*</span></label>
            <input 
              type="date" 
              id="fecha_salida" 
              name="fecha_salida" 
              min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
              value="<?php echo htmlspecialchars($data['fecha_salida'] ?? ''); ?>"
              class="<?php echo isset($errors['fecha_salida']) ? 'input-error' : ''; ?>"
              onchange="fetchAvailableRooms()"
            >
            <span class="input-hint">La salida debe ser a más tardar a las 10:00 AM</span>
            <?php if (isset($errors['fecha_salida'])): ?>
              <span class="error-text"><?php echo $errors['fecha_salida']; ?></span>
            <?php endif; ?>
          </div>
        </div>
      </fieldset>

      <!-- Section 2: Huésped y Habitación -->
      <fieldset class="form-fieldset">
        <legend><i class="fa-solid fa-hotel"></i> Asignación de Huésped y Habitación</legend>
        
        <div class="form-row">
          <div class="form-group col-9">
            <label for="guest_uuid">Seleccionar Huésped <span class="required">*</span></label>
            <select 
              id="guest_uuid" 
              name="guest_uuid" 
              class="<?php echo isset($errors['guest_uuid']) ? 'input-error' : ''; ?>"
            >
              <option value="">Seleccione un huésped...</option>
              <?php foreach ($guests as $g): ?>
                <option value="<?php echo $g->uuid; ?>" <?php echo ($data['guest_uuid'] ?? '') === $g->uuid ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($g->nombreCompleto) . " (" . htmlspecialchars($g->tipoDocumento) . " " . htmlspecialchars($g->numeroDocumento) . ")"; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['guest_uuid'])): ?>
              <span class="error-text"><?php echo $errors['guest_uuid']; ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group col-3 flex-end-btn">
            <button type="button" class="btn btn-secondary btn-block" onclick="openNewGuestDialog()">
              <i class="fa-solid fa-user-plus"></i> Nuevo
            </button>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-8">
            <label for="numero_habitacion">Habitación Disponible <span class="required">*</span></label>
            <select 
              id="numero_habitacion" 
              name="numero_habitacion" 
              class="<?php echo isset($errors['numero_habitacion']) ? 'input-error' : ''; ?>"
            >
              <option value="">Seleccione primero las fechas...</option>
              <?php foreach ($availableRooms as $r): ?>
                <?php
                $cap = 1;
                if ($r['tipo_habitacion'] === 'Sencilla') $cap = 1;
                elseif ($r['tipo_habitacion'] === 'Doble') $cap = 2;
                elseif ($r['tipo_habitacion'] === 'Suite') $cap = 4;
                elseif ($r['tipo_habitacion'] === 'Familiar') $cap = 6;
                ?>
                <option value="<?php echo $r['numero_habitacion']; ?>" 
                        data-capacidad="<?php echo $cap; ?>"
                        <?php echo ($data['numero_habitacion'] ?? '') === $r['numero_habitacion'] ? 'selected' : ''; ?>>
                  Habitación <?php echo htmlspecialchars($r['numero_habitacion']) . " (" . htmlspecialchars($r['tipo_habitacion']) . " - Máx. " . $cap . " pers.)"; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <span class="input-hint text-info" id="rooms-loading-msg" style="display:none;"><i class="fa-solid fa-spinner fa-spin"></i> Cargando habitaciones disponibles...</span>
            <?php if (isset($errors['numero_habitacion'])): ?>
              <span class="error-text"><?php echo $errors['numero_habitacion']; ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group col-4">
            <label for="numero_huespedes">Número de Huéspedes <span class="required">*</span></label>
            <input 
              type="number" 
              id="numero_huespedes" 
              name="numero_huespedes" 
              min="1" 
              max="10"
              value="<?php echo htmlspecialchars($data['numero_huespedes'] ?? '1'); ?>"
              class="<?php echo isset($errors['numero_huespedes']) ? 'input-error' : ''; ?>"
            >
            <?php if (isset($errors['numero_huespedes'])): ?>
              <span class="error-text"><?php echo $errors['numero_huespedes']; ?></span>
            <?php endif; ?>
          </div>
        </div>
      </fieldset>

      <!-- Submit Section -->
      <div class="form-actions-bar">
        <a href="index.php?controller=reservations&action=index" class="btn btn-secondary">
          <i class="fa-solid fa-xmark"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-calendar-check"></i> Confirmar Reserva
        </button>
      </div>

    </form>
  </div>
</div>

<!-- Native HTML Dialog for Quick Guest Registration -->
<dialog id="quick-guest-dialog" class="glass-dialog">
  <div class="dialog-header">
    <h3><i class="fa-solid fa-user-plus text-primary"></i> Registro Rápido de Huésped</h3>
    <button class="btn-close-dialog" onclick="closeNewGuestDialog()">&times;</button>
  </div>
  
  <div class="dialog-body">
    <div id="dialog-error" class="alert alert-danger" style="display:none;"></div>
    
    <form id="quick-guest-form" onsubmit="submitQuickGuestForm(event)">
      <div class="form-row">
        <div class="form-group col-8">
          <label for="dialog_nombre">Nombre Completo <span class="required">*</span></label>
          <input type="text" id="dialog_nombre" name="nombre_completo" required placeholder="Ej. Ana Diaz">
        </div>
        <div class="form-group col-4">
          <label for="dialog_edad">Edad <span class="required">*</span></label>
          <input type="number" id="dialog_edad" name="edad" min="1" max="120" required placeholder="30">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-4">
          <label for="dialog_tipo_doc">Documento <span class="required">*</span></label>
          <select id="dialog_tipo_doc" name="tipo_documento" required>
            <option value="CC">CC</option>
            <option value="TI">TI</option>
            <option value="CE">CE</option>
            <option value="Pasaporte">Pasaporte</option>
          </select>
        </div>
        <div class="form-group col-8">
          <label for="dialog_num_doc">Número <span class="required">*</span></label>
          <input type="text" id="dialog_num_doc" name="numero_documento" required placeholder="Ej. 10203040">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-12">
          <label for="dialog_direccion">Dirección <span class="required">*</span></label>
          <input type="text" id="dialog_direccion" name="direccion" required placeholder="Ej. Calle 10 # 5-20">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-6">
          <label for="dialog_celular">Celular <span class="required">*</span></label>
          <input type="tel" id="dialog_celular" name="celular" required placeholder="Ej. 3101234567">
        </div>
        <div class="form-group col-6">
          <label for="dialog_email">Correo <span class="required">*</span></label>
          <input type="email" id="dialog_email" name="email" required placeholder="Ej. ana@email.com">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-7">
          <label for="dialog_contacto">Contacto Emergencia <span class="required">*</span></label>
          <input type="text" id="dialog_contacto" name="contacto_emergencia" required placeholder="Nombre de contacto">
        </div>
        <div class="form-group col-5">
          <label for="dialog_parentesco">Parentesco <span class="required">*</span></label>
          <select id="dialog_parentesco" name="parentesco_contacto" required>
            <option value="Madre">Madre</option>
            <option value="Padre">Padre</option>
            <option value="Esposo(a)">Esposo(a)</option>
            <option value="Hijo(a)">Hijo(a)</option>
            <option value="Hermano(a)">Hermano(a)</option>
            <option value="Otro">Otro</option>
          </select>
        </div>
      </div>

      <div class="dialog-footer">
        <button type="button" class="btn btn-secondary" onclick="closeNewGuestDialog()">Cancelar</button>
        <button type="submit" class="btn btn-primary">Registrar y Seleccionar</button>
      </div>
    </form>
  </div>
</dialog>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
