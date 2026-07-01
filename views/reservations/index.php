<?php
// views/reservations/index.php
require_once __DIR__ . '/../layouts/header.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!-- Feedback Alerts -->
<?php if ($success === 'created'): ?>
  <div class="alert alert-success">
    <i class="fa-solid fa-circle-check"></i>
    <span>¡Reserva registrada con éxito! La habitación ha sido asignada.</span>
  </div>
<?php elseif ($success === 'updated'): ?>
  <div class="alert alert-success">
    <i class="fa-solid fa-circle-check"></i>
    <span>¡La reserva ha sido modificada correctamente!</span>
  </div>
<?php elseif ($success === 'cancelled'): ?>
  <div class="alert alert-success">
    <i class="fa-solid fa-circle-check"></i>
    <span>La reserva ha sido cancelada y la habitación se encuentra disponible nuevamente.</span>
  </div>
<?php endif; ?>

<?php if ($error === 'notfound'): ?>
  <div class="alert alert-danger">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span>La reserva seleccionada no fue encontrada.</span>
  </div>
<?php elseif ($error === 'cancel_failed'): ?>
  <div class="alert alert-danger">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span>Ocurrió un error al intentar cancelar la reserva.</span>
  </div>
<?php endif; ?>

<div class="glass-panel">
  <div class="panel-header-search">
    <div class="search-box-wrapper">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="reservation-search" placeholder="Buscar por huésped, habitación o estado..." onkeyup="filterReservationsTable()">
    </div>
    <a href="index.php?controller=reservations&action=create" class="btn btn-primary">
      <i class="fa-solid fa-calendar-plus"></i>
      <span>Nueva Reserva</span>
    </a>
  </div>

  <div class="panel-body">
    <?php if (empty($reservations)): ?>
      <div class="empty-state">
        <i class="fa-solid fa-calendar-days"></i>
        <p>No se registran reservas en el sistema.</p>
        <a href="index.php?controller=reservations&action=create" class="btn btn-outline btn-sm">Crear una Reserva</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table" id="reservations-table">
          <thead>
            <tr>
              <th>Huésped</th>
              <th>Habitación / Tipo</th>
              <th>Fecha Check-In</th>
              <th>Fecha Check-Out</th>
              <th>Nro. Huéspedes</th>
              <th>Estado</th>
              <th class="text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reservations as $res): ?>
              <tr>
                <td class="font-medium reservation-guest-cell"><?php echo htmlspecialchars($res->guestName); ?></td>
                <td>
                  <div class="room-details-cell">
                    <span class="badge badge-outline">Hab. <?php echo htmlspecialchars($res->numeroHabitacion); ?></span>
                    <span class="text-xs text-muted"><?php echo htmlspecialchars($res->tipoHabitacion); ?></span>
                  </div>
                </td>
                <td>
                  <i class="fa-regular fa-calendar-plus text-success"></i>
                  <?php echo date('d/m/Y', strtotime($res->fechaIngreso)); ?>
                </td>
                <td>
                  <i class="fa-regular fa-calendar-minus text-danger"></i>
                  <?php echo date('d/m/Y', strtotime($res->fechaSalida)); ?>
                </td>
                <td><?php echo htmlspecialchars($res->numeroHuespedes); ?> pers.</td>
                <td>
                  <span class="badge reservation-status-cell <?php echo $res->estado === 'Confirmada' ? 'badge-success' : 'badge-danger'; ?>">
                    <?php echo htmlspecialchars($res->estado); ?>
                  </span>
                </td>
                <td class="text-right">
                  <div class="actions-wrapper">
                    <?php if ($res->estado === 'Confirmada'): ?>
                      <a href="index.php?controller=reservations&action=edit&uuid=<?php echo $res->uuid; ?>" class="btn-action btn-edit" title="Editar Reserva">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </a>
                      <a href="index.php?controller=reservations&action=cancel&uuid=<?php echo $res->uuid; ?>" 
                         class="btn-action btn-cancel" 
                         title="Cancelar Reserva"
                         onclick="return confirm('¿Está seguro de que desea cancelar esta reserva? La habitación volverá a estar disponible.');">
                        <i class="fa-solid fa-ban"></i>
                      </a>
                    <?php else: ?>
                      <span class="text-muted text-xs">Sin acciones</span>
                    <?php endif; ?>
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
