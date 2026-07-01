<?php
// views/guests/index.php
require_once __DIR__ . '/../layouts/header.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!-- Feedback Alerts -->
<?php if ($success === 'created'): ?>
  <div class="alert alert-success">
    <i class="fa-solid fa-circle-check"></i>
    <span>¡Huésped registrado exitosamente!</span>
  </div>
<?php elseif ($success === 'updated'): ?>
  <div class="alert alert-success">
    <i class="fa-solid fa-circle-check"></i>
    <span>¡Datos del huésped actualizados correctamente!</span>
  </div>
<?php elseif ($success === 'deleted'): ?>
  <div class="alert alert-success">
    <i class="fa-solid fa-circle-check"></i>
    <span>Huésped eliminado del sistema de forma segura.</span>
  </div>
<?php endif; ?>

<?php if ($error === 'notfound'): ?>
  <div class="alert alert-danger">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span>El huésped no existe o no fue encontrado.</span>
  </div>
<?php elseif ($error === 'delete_failed'): ?>
  <div class="alert alert-danger">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span>No se pudo eliminar al huésped porque tiene reservas activas.</span>
  </div>
<?php endif; ?>

<div class="glass-panel">
  <div class="panel-header-search">
    <div class="search-box-wrapper">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="guest-search" placeholder="Buscar por nombre, documento o correo..." onkeyup="filterGuestsTable()">
    </div>
    <a href="index.php?controller=guests&action=create" class="btn btn-primary">
      <i class="fa-solid fa-user-plus"></i>
      <span>Registrar Huésped</span>
    </a>
  </div>
  
  <div class="panel-body">
    <?php if (empty($guests)): ?>
      <div class="empty-state">
        <i class="fa-solid fa-users-slash"></i>
        <p>No hay huéspedes registrados en el sistema.</p>
        <a href="index.php?controller=guests&action=create" class="btn btn-outline btn-sm">Registrar el Primero</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table" id="guests-table">
          <thead>
            <tr>
              <th>Nombre Completo</th>
              <th>Documento</th>
              <th>Contacto</th>
              <th>Edad</th>
              <th>Contacto Emergencia</th>
              <th class="text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($guests as $g): ?>
              <tr>
                <td class="font-medium guest-name-cell"><?php echo htmlspecialchars($g->nombreCompleto); ?></td>
                <td>
                  <span class="doc-badge"><?php echo htmlspecialchars($g->tipoDocumento); ?></span>
                  <span class="guest-doc-cell"><?php echo htmlspecialchars($g->numeroDocumento); ?></span>
                </td>
                <td>
                  <div class="guest-contact-info">
                    <span class="guest-email-cell"><i class="fa-regular fa-envelope"></i> <?php echo htmlspecialchars($g->email); ?></span>
                    <span><i class="fa-solid fa-mobile-screen-button"></i> <?php echo htmlspecialchars($g->celular); ?></span>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($g->edad); ?> años</td>
                <td>
                  <div class="emergency-contact-info">
                    <span class="font-medium"><?php echo htmlspecialchars($g->contactoEmergencia); ?></span>
                    <span class="text-xs text-muted">(<?php echo htmlspecialchars($g->parentescoContacto); ?>)</span>
                  </div>
                </td>
                <td class="text-right actions-cell">
                  <div class="actions-wrapper">
                    <a href="index.php?controller=guests&action=edit&uuid=<?php echo $g->uuid; ?>" class="btn-action btn-edit" title="Editar Huésped">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a href="index.php?controller=guests&action=delete&uuid=<?php echo $g->uuid; ?>" 
                       class="btn-action btn-delete" 
                       title="Eliminar Huésped"
                       onclick="return confirm('¿Está seguro de que desea eliminar a este huésped? Se eliminarán todas sus reservas asociadas.');">
                      <i class="fa-solid fa-trash-can"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
