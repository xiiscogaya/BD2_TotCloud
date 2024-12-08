<?php
session_start();
include '../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Datos principales de PaaS
    $nombre = trim($_POST['nombre']);
    $estado = trim($_POST['estado']);
    $idSO = intval($_POST['idSO']);

    // Datos de componentes seleccionados
    $cpus = $_POST['cpus'] ?? [];
    $rams = $_POST['rams'] ?? [];
    $almacenamientos = $_POST['almacenamientos'] ?? [];
    $ips = $_POST['ips'] ?? [];

    if (empty($nombre) || empty($estado) || $idSO <= 0) {
        $message = 'Todos los campos son obligatorios.';
    } else {
        // Crear configuración PaaS
        $query_paas = "INSERT INTO paas (Nombre, Estado, idSO) VALUES (?, ?, ?)";
        $stmt_paas = $conn->prepare($query_paas);
        $stmt_paas->bind_param('ssi', $nombre, $estado, $idSO);

        if ($stmt_paas->execute()) {
            $idPaaS = $conn->insert_id;

            // Insertar componentes seleccionados
            $insert_component = function($table, $idPaaS, $component, $cantidad) use ($conn) {
                $query = "INSERT INTO $table (idPaaS, $component, Cantidad) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('iii', $idPaaS, $component, $cantidad);
                $stmt->execute();
            };

            // Insertar CPUs
            foreach ($cpus as $cpu) {
                $insert_component('R_PaaS_CPU', $idPaaS, 'idCPU', $cpu);
            }

            // Insertar RAMs
            foreach ($rams as $ram) {
                $insert_component('R_PaaS_RAM', $idPaaS, 'idRAM', $ram);
            }

            // Insertar Almacenamientos
            foreach ($almacenamientos as $almacenamiento) {
                $insert_component('R_PaaS_Almacenamiento', $idPaaS, 'idAlmacenamiento', $almacenamiento);
            }

            // Insertar IPs
            foreach ($ips as $ip) {
                $insert_component('R_PaaS_IP', $idPaaS, 'idIp', $ip);
            }

            $message = 'Configuración PaaS creada exitosamente.';
        } else {
            $message = 'Error al crear la configuración PaaS.';
        }
    }
}

// Obtener datos de componentes
$cpus = $conn->query("SELECT * FROM cpu");
$rams = $conn->query("SELECT * FROM ram");
$almacenamientos = $conn->query("SELECT * FROM almacenamiento");
$ips = $conn->query("SELECT * FROM direccionip");
$sos = $conn->query("SELECT * FROM sistemaoperativo");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear PaaS - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Crear Configuración PaaS</h1>
    </header>

    <main class="container my-5">
        <h2 class="text-center">Nueva Configuración PaaS</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info text-center"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="crear_paas.php" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="Activo">Activo</option>
                    <option value="En pruebas">En pruebas</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="idSO" class="form-label">Sistema Operativo</label>
                <select class="form-select" id="idSO" name="idSO" required>
                    <option value="">Selecciona un Sistema Operativo</option>
                    <?php while ($row_so = $sos->fetch_assoc()): ?>
                        <option value="<?php echo $row_so['idSO']; ?>"><?php echo htmlspecialchars($row_so['Nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar CPUs</label>
                <select class="form-select" name="cpus[]" multiple required>
                    <?php while ($row = $cpus->fetch_assoc()): ?>
                        <option value="<?php echo $row['idCPU']; ?>"><?php echo htmlspecialchars($row['Nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
                <small class="form-text text-muted">Mantén presionado Ctrl (Cmd en Mac) para seleccionar varias opciones.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar RAM</label>
                <select class="form-select" name="rams[]" multiple required>
                    <?php while ($row = $rams->fetch_assoc()): ?>
                        <option value="<?php echo $row['idRAM']; ?>"><?php echo htmlspecialchars($row['Nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar Almacenamientos</label>
                <select class="form-select" name="almacenamientos[]" multiple required>
                    <?php while ($row = $almacenamientos->fetch_assoc()): ?>
                        <option value="<?php echo $row['idAlmacenamiento']; ?>"><?php echo htmlspecialchars($row['Nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar IPs</label>
                <select class="form-select" name="ips[]" multiple required>
                    <?php while ($row = $ips->fetch_assoc()): ?>
                        <option value="<?php echo $row['idIp']; ?>"><?php echo htmlspecialchars($row['Direccion']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Crear Configuración</button>
        </form>
    </main>


</body>
</html>
