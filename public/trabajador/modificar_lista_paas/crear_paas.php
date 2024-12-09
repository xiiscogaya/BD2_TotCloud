<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Datos principales de PaaS
    $nombre = trim($_POST['nombre']);
    $idSO = intval($_POST['idSO']);
    $idIp = intval($_POST['idIp']); // IP seleccionada
    $estado = "En pruebas"; // Estado predefinido

    // Datos de componentes seleccionados
    $cpus = $_POST['cpus'] ?? [];
    $rams = $_POST['rams'] ?? [];
    $almacenamientos = $_POST['almacenamientos'] ?? [];

    if (empty($nombre) || $idSO <= 0 || $idIp <= 0) {
        $message = 'Todos los campos son obligatorios.';
    } else {
        // Verificar que la IP no esté ya asociada a otra configuración
        $check_ip_query = "SELECT idPaaS FROM direccionip WHERE idIp = ? AND idPaaS IS NOT NULL";
        $stmt_check_ip = $conn->prepare($check_ip_query);
        $stmt_check_ip->bind_param('i', $idIp);
        $stmt_check_ip->execute();
        $result_ip = $stmt_check_ip->get_result();

        if ($result_ip->num_rows > 0) {
            $message = 'La IP seleccionada ya está asociada a otra configuración.';
        } else {
            // Determinar el siguiente ID disponible para PaaS
            $query_next_id = "SELECT COALESCE(MAX(idPaaS), 0) + 1 AS next_id FROM paas";
            $result_next_id = $conn->query($query_next_id);
            $row_next_id = $result_next_id->fetch_assoc();
            $idPaaS = $row_next_id['next_id'];

            // Crear configuración PaaS
            $query_paas = "INSERT INTO paas (idPaaS, Nombre, Estado, idSO) VALUES (?, ?, ?, ?)";
            $stmt_paas = $conn->prepare($query_paas);
            $stmt_paas->bind_param('issi', $idPaaS, $nombre, $estado, $idSO);

            if ($stmt_paas->execute()) {
                // Asociar la IP a esta configuración PaaS
                $update_ip_query = "UPDATE direccionip SET idPaaS = ? WHERE idIp = ?";
                $stmt_ip = $conn->prepare($update_ip_query);
                $stmt_ip->bind_param('ii', $idPaaS, $idIp);
                $stmt_ip->execute();

                // Insertar componentes seleccionados y actualizar cantidades disponibles
                $insert_component = function($table, $idPaaS, $component_column, $component_id, $cantidad, $update_table) use ($conn) {
                    // Insertar en la tabla de relación
                    $query = "INSERT INTO $table (idPaaS, $component_column, Cantidad) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('iii', $idPaaS, $component_id, $cantidad);
                    $stmt->execute();

                    // Restar la cantidad en la tabla principal
                    $update_query = "UPDATE $update_table SET Cantidad = Cantidad - ? WHERE $component_column = ?";
                    $stmt_update = $conn->prepare($update_query);
                    $stmt_update->bind_param('ii', $cantidad, $component_id);
                    $stmt_update->execute();
                };

                // Insertar CPUs seleccionadas
                foreach ($cpus as $cpu) {
                    if (!empty($_POST["cantidad_cpu_$cpu"])) {
                        $cantidad = intval($_POST["cantidad_cpu_$cpu"]);
                        $insert_component('R_PaaS_CPU', $idPaaS, 'idCPU', $cpu, $cantidad, 'cpu');
                    }
                }

                // Insertar RAMs seleccionadas
                foreach ($rams as $ram) {
                    if (!empty($_POST["cantidad_ram_$ram"])) {
                        $cantidad = intval($_POST["cantidad_ram_$ram"]);
                        $insert_component('R_PaaS_RAM', $idPaaS, 'idRAM', $ram, $cantidad, 'ram');
                    }
                }

                // Insertar Almacenamientos seleccionados
                foreach ($almacenamientos as $almacenamiento) {
                    if (!empty($_POST["cantidad_almacenamiento_$almacenamiento"])) {
                        $cantidad = intval($_POST["cantidad_almacenamiento_$almacenamiento"]);
                        $insert_component('R_PaaS_Almacenamiento', $idPaaS, 'idAlmacenamiento', $almacenamiento, $cantidad, 'almacenamiento');
                    }
                }

                // Guardar mensaje en sesión
                $_SESSION['success_message_crear'] = 'Configuración PaaS creada exitosamente.';

                // Redirigir a modificar_paas.php
                header('Location: modificar_paas.php');
            } else {
                $message = 'Error al crear la configuración PaaS.';
            }
        }
    }
}

// Obtener datos de componentes
$cpus = $conn->query("SELECT * FROM cpu WHERE Cantidad > 0");
$rams = $conn->query("SELECT * FROM ram WHERE Cantidad > 0");
$almacenamientos = $conn->query("SELECT * FROM almacenamiento WHERE Cantidad > 0");
$ips = $conn->query("SELECT * FROM direccionip WHERE idPaaS IS NULL"); // Solo IPs no asociadas
$sos = $conn->query("SELECT * FROM sistemaoperativo");
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear PaaS - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Crear Configuración PaaS</h1>
    </header>

    <main class="container my-5">
        <!-- Botón de Volver a Trabajador -->
        <div class="container my-3">
            <a href="modificar_paas.php" class="btn btn-secondary">Volver</a>
        </div>
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
                <?php while ($row = $cpus->fetch_assoc()): ?>
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="cpus[]" value="<?php echo $row['idCPU']; ?>">
                        <span class="ms-2"><?php echo htmlspecialchars($row['Nombre'] . ' - ' . $row['Nucleos'] . ' núcleos a ' . $row['Frecuencia'] . 'GHz (Máximo: ' . $row['Cantidad'] . ')'); ?></span>
                        <input type="number" 
                               name="cantidad_cpu_<?php echo $row['idCPU']; ?>" 
                               min="1" 
                               max="<?php echo $row['Cantidad']; ?>" 
                               class="form-control ms-3" 
                               style="max-width: 100px;" 
                               placeholder="Máx: <?php echo $row['Cantidad']; ?>">
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar RAM</label>
                <?php while ($row = $rams->fetch_assoc()): ?>
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="rams[]" value="<?php echo $row['idRAM']; ?>">
                        <span class="ms-2"><?php echo htmlspecialchars($row['Nombre'] . ' - ' . $row['Capacidad'] . 'GB a ' . $row['Frecuencia'] . 'MHz (Máximo: ' . $row['Cantidad'] . ')'); ?></span>
                        <input type="number" 
                               name="cantidad_ram_<?php echo $row['idRAM']; ?>" 
                               min="1" 
                               max="<?php echo $row['Cantidad']; ?>" 
                               class="form-control ms-3" 
                               style="max-width: 100px;" 
                               placeholder="Máx: <?php echo $row['Cantidad']; ?>">
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar Almacenamientos</label>
                <?php while ($row = $almacenamientos->fetch_assoc()): ?>
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="almacenamientos[]" value="<?php echo $row['idAlmacenamiento']; ?>">
                        <span class="ms-2"><?php echo htmlspecialchars($row['Nombre'] . ' - ' . $row['Capacidad'] . 'GB ' . $row['Tipo'] . ' (Lectura: ' . $row['VelocidadLectura'] . 'MB/s, Escritura: ' . $row['VelocidadEscritura'] . 'MB/s) Máximo: ' . $row['Cantidad']); ?></span>
                        <input type="number" 
                               name="cantidad_almacenamiento_<?php echo $row['idAlmacenamiento']; ?>" 
                               min="1" 
                               max="<?php echo $row['Cantidad']; ?>" 
                               class="form-control ms-3" 
                               style="max-width: 100px;" 
                               placeholder="Máx: <?php echo $row['Cantidad']; ?>">
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar IP</label>
                <select class="form-select" name="idIp" required>
                    <option value="">Selecciona una IP</option>
                    <?php while ($row = $ips->fetch_assoc()): ?>
                        <option value="<?php echo $row['idIp']; ?>">
                            <?php echo htmlspecialchars($row['Direccion']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Crear Configuración</button>
        </form>
    </main>
</body>
</html>
