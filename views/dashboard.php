<?php
// views/dashboard.php
require_once __DIR__ . '/layouts/header.php';
?>

<!-- Statistics Overview -->
<div class="stats-grid">
  <!-- Card 1: Guests -->
  <div class="stat-card card-purple">
    <div class="stat-icon">
      <i class="fa-solid fa-users"></i>
    </div>
    <div class="stat-details">
      <h3><?php echo number_format($totalGuests); ?></h3>
      <p>Huéspedes Registrados</p>
    </div>
    <div class="stat-footer">
      <a href="index.php?controller=guests&action=index">Ver listado <i class="fa-solid fa-arrow-right-long"></i></a>
    </div>
  </div>

  <!-- Card 2: Active Bookings -->
  <div class="stat-card card-blue">
    <div class="stat-icon">
      <i class="fa-solid fa-calendar-check"></i>
    </div>
    <div class="stat-details">
      <h3><?php echo number_format($activeReservations); ?></h3>
      <p>Reservas Activas</p>
    </div>
    <div class="stat-footer">
      <a href="index.php?controller=reservations&action=index">Ver reservas <i class="fa-solid fa-arrow-right-long"></i></a>
    </div>
  </div>

  <!-- Card 3: Room Occupancy -->
  <div class="stat-card card-teal">
    <div class="stat-icon">
      <i class="fa-solid fa-door-open"></i>
    </div>
    <div class="stat-details">
      <h3><?php echo $occupancyRate; ?>%</h3>
      <p>Ocupación Hoy (<?php echo $occupiedRoomsToday; ?>/<?php echo $totalRooms; ?> habs)</p>
    </div>
    <div class="stat-footer">
      <span>Capacidad total del hotel</span>
    </div>
  </div>
</div>

<div class="dashboard-row">
  <!-- Left Side: Recent Reservations -->
  <div class="dashboard-col col-7">
    <div class="glass-panel">
      <div class="panel-header">
        <h2><i class="fa-solid fa-clock-rotate-left header-icon"></i> Reservas Recientes</h2>
        <a href="index.php?controller=reservations&action=create" class="btn btn-sm btn-primary">
          <i class="fa-solid fa-plus"></i> Nueva Reserva
        </a>
      </div>
      <div class="panel-body">
        <?php if (empty($recentReservations)): ?>
          <div class="empty-state">
            <i class="fa-solid fa-calendar-xmark"></i>
            <p>No se registran reservas en el sistema.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Huésped</th>
                  <th>Habitación</th>
                  <th>Check-In</th>
                  <th>Check-Out</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentReservations as $res): ?>
                  <tr>
                    <td class="font-medium"><?php echo htmlspecialchars($res['guest_name']); ?></td>
                    <td>
                      <span class="badge badge-outline">Hab. <?php echo htmlspecialchars($res['numero_habitacion']); ?></span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($res['fecha_ingreso'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($res['fecha_salida'])); ?></td>
                    <td>
                      <span class="badge <?php echo $res['estado'] === 'Confirmada' ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo htmlspecialchars($res['estado']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right Side: Python Analytics Module -->
  <div class="dashboard-col col-5">
    <div class="glass-panel">
      <div class="panel-header">
        <h2><i class="fa-brands fa-python header-icon text-python"></i> Reporte Estadístico Python</h2>
      </div>
      <div class="panel-body">
        <p class="panel-desc">
          Este módulo ejecuta un script en **Python 3** en el servidor para realizar análisis de datos sobre las reservas (duración promedio de estadías, distribución de habitaciones y demografía de huéspedes).
        </p>

        <!-- Python Report Output Container -->
        <?php if ($pythonReport): ?>
          <?php if ($pythonReport['success']): ?>
            <div class="python-report-success">
              <div class="report-meta">
                <span><i class="fa-solid fa-circle-check"></i> Reporte generado con éxito</span>
                <span class="time"><?php echo date('H:i:s'); ?></span>
              </div>
              <pre class="terminal-output"><code><?php echo htmlspecialchars($pythonReport['content']); ?></code></pre>
            </div>
          <?php else: ?>
            <div class="alert alert-danger">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <span><?php echo htmlspecialchars($pythonReport['message']); ?></span>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <div class="python-action-area">
          <a href="index.php?controller=dashboard&action=generateReport" class="btn btn-secondary btn-block">
            <i class="fa-brands fa-python"></i>
            <span>Generar Reporte Analítico</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/layouts/footer.php';
?>
