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
    $estado = "En pruebas"; // Estado predefinido
    $idCPU = intval($_POST['idCPU']);
    $cantidadCPU = intval($_POST['cantidad_cpu']);
    $idRAM = intval($_POST['idRAM']);
    $cantidadRAM = intval($_POST['cantidad_ram']);
    $almacenamientos = $_POST['almacenamientos'] ?? [];
    $idIp = intval($_POST['idIp']); // Dirección IP seleccionada

    if (empty($nombre) || $idCPU <= 0 || $cantidadCPU <= 0 || $idRAM <= 0 || $cantidadRAM <= 0 || $idIp <= 0) {
        $message = 'Todos los campos son obligatorios.';
    } else {
        // Verificar que la cantidad de CPU y RAM no exceda lo disponible
        $cpu_check_query = "SELECT Cantidad FROM cpu WHERE idCPU = ?";
        $stmt_cpu_check = $conn->prepare($cpu_check_query);
        $stmt_cpu_check->bind_param('i', $idCPU);
        $stmt_cpu_check->execute();
        $result_cpu_check = $stmt_cpu_check->get_result();
        $availableCPU = $result_cpu_check->fetch_assoc()['Cantidad'];

        $ram_check_query = "SELECT Cantidad FROM ram WHERE idRAM = ?";
        $stmt_ram_check = $conn->prepare($ram_check_query);
        $stmt_ram_check->bind_param('i', $idRAM);
        $stmt_ram_check->execute();
        $result_ram_check = $stmt_ram_check->get_result();
        $availableRAM = $result_ram_check->fetch_assoc()['Cantidad'];

        if ($cantidadCPU > $availableCPU || $cantidadRAM > $availableRAM) {
            $message = 'La cantidad seleccionada de CPU o RAM excede el stock disponible.';
        } else {
            // Determinar el siguiente ID disponible para PaaS
            $query_next_id = "SELECT COALESCE(MIN(a.idPaaS)+1, 1) AS next_id FROM paas a LEFT JOIN paas b ON a.idPaaS = b.idPaaS-1 WHERE b.idPaaS IS NULL";
            $result_next_id = $conn->query($query_next_id);
            $idPaaS = $result_next_id->fetch_assoc()['next_id'];

            // Crear configuración PaaS
            $query_paas = "INSERT INTO paas (idPaaS, Nombre, Estado) VALUES (?, ?, ?)";
            $stmt_paas = $conn->prepare($query_paas);
            $stmt_paas->bind_param('iss', $idPaaS, $nombre, $estado);

            if ($stmt_paas->execute()) {
                // Asociar dirección IP a este PaaS
                $query_update_ip = "UPDATE direccionip SET idPaaS = ? WHERE idIp = ?";
                $stmt_update_ip = $conn->prepare($query_update_ip);
                $stmt_update_ip->bind_param('ii', $idPaaS, $idIp);
                $stmt_update_ip->execute();

                // Insertar CPU seleccionada
                $query_cpu = "INSERT INTO R_PaaS_CPU (idPaaS, idCPU, Cantidad) VALUES (?, ?, ?)";
                $stmt_cpu = $conn->prepare($query_cpu);
                $stmt_cpu->bind_param('iii', $idPaaS, $idCPU, $cantidadCPU);
                $stmt_cpu->execute();

                // Actualizar stock de CPU
                $query_update_cpu = "UPDATE cpu SET Cantidad = Cantidad - ? WHERE idCPU = ?";
                $stmt_update_cpu = $conn->prepare($query_update_cpu);
                $stmt_update_cpu->bind_param('ii', $cantidadCPU, $idCPU);
                $stmt_update_cpu->execute();

                // Insertar RAM seleccionada
                $query_ram = "INSERT INTO R_PaaS_RAM (idPaaS, idRAM, Cantidad) VALUES (?, ?, ?)";
                $stmt_ram = $conn->prepare($query_ram);
                $stmt_ram->bind_param('iii', $idPaaS, $idRAM, $cantidadRAM);
                $stmt_ram->execute();

                // Actualizar stock de RAM
                $query_update_ram = "UPDATE ram SET Cantidad = Cantidad - ? WHERE idRAM = ?";
                $stmt_update_ram = $conn->prepare($query_update_ram);
                $stmt_update_ram->bind_param('ii', $cantidadRAM, $idRAM);
                $stmt_update_ram->execute();

                // Insertar Almacenamientos seleccionados
                foreach ($almacenamientos as $almacenamiento) {
                    if (!empty($_POST["cantidad_almacenamiento_$almacenamiento"])) {
                        $cantidad = intval($_POST["cantidad_almacenamiento_$almacenamiento"]);

                        // Insertar en la tabla de relación
                        $query_storage = "INSERT INTO R_PaaS_Almacenamiento (idPaaS, idAlmacenamiento, Cantidad) VALUES (?, ?, ?)";
                        $stmt_storage = $conn->prepare($query_storage);
                        $stmt_storage->bind_param('iii', $idPaaS, $almacenamiento, $cantidad);
                        $stmt_storage->execute();

                        // Actualizar stock de almacenamiento
                        $query_update_storage = "UPDATE almacenamiento SET Cantidad = Cantidad - ? WHERE idAlmacenamiento = ?";
                        $stmt_update_storage = $conn->prepare($query_update_storage);
                        $stmt_update_storage->bind_param('ii', $cantidad, $almacenamiento);
                        $stmt_update_storage->execute();
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
$ips_disponibles = $conn->query("SELECT * FROM direccionip WHERE idPaaS IS NULL");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear PaaS - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Crear Configuración PaaS</h1>
    </header>

    <main class="container my-5">
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
                <label for="idCPU" class="form-label">Seleccionar CPU</label>
                <select class="form-select" id="idCPU" name="idCPU" required>
                    <option value="" data-max="0">Selecciona un tipo de CPU</option>
                    <?php while ($row = $cpus->fetch_assoc()): ?>
                        <option value="<?php echo $row['idCPU']; ?>" data-max="<?php echo $row['Cantidad']; ?>">
                            <?php echo htmlspecialchars($row['Nombre'] . ' - ' . $row['Nucleos'] . ' núcleos (Disponible: ' . $row['Cantidad'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="number" class="form-control mt-2" id="cantidadCPU" name="cantidad_cpu" min="1" placeholder="Cantidad de CPU" required>
            </div>

            <div class="mb-3">
                <label for="idRAM" class="form-label">Seleccionar RAM</label>
                <select class="form-select" id="idRAM" name="idRAM" required>
                    <option value="" data-max="0">Selecciona un tipo de RAM</option>
                    <?php while ($row = $rams->fetch_assoc()): ?>
                        <option value="<?php echo $row['idRAM']; ?>" data-max="<?php echo $row['Cantidad']; ?>">
                            <?php echo htmlspecialchars($row['Nombre'] . ' - ' . $row['Capacidad'] . 'GB (Disponible: ' . $row['Cantidad'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="number" class="form-control mt-2" id="cantidadRAM" name="cantidad_ram" min="1" placeholder="Cantidad de RAM" required>
            </div>

            <div class="mb-3">
                <label for="idIp" class="form-label">Seleccionar Dirección IP</label>
                <select class="form-select" id="idIp" name="idIp" required>
                    <option value="">Seleccione una dirección IP</option>
                    <?php while ($row = $ips_disponibles->fetch_assoc()): ?>
                        <option value="<?php echo $row['idIp']; ?>">
                            <?php echo htmlspecialchars($row['Direccion'] . ' (Precio: ' . $row['PrecioH'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar Almacenamientos</label>
                <?php while ($row = $almacenamientos->fetch_assoc()): ?>
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="almacenamientos[]" value="<?php echo $row['idAlmacenamiento']; ?>">
                        <span class="ms-2">
                            <?php echo htmlspecialchars($row['Nombre'] . ' - ' . $row['Capacidad'] . 'GB'); ?>
                            <strong class="text-muted ms-2">(MAX = <?php echo $row['Cantidad']; ?>)</strong>
                        </span>
                        <input type="number" 
                               name="cantidad_almacenamiento_<?php echo $row['idAlmacenamiento']; ?>" 
                               min="1" 
                               max="<?php echo $row['Cantidad']; ?>" 
                               class="form-control ms-3" 
                               style="max-width: 100px;" 
                               placeholder="Cantidad">
                    </div>
                <?php endwhile; ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">Crear Configuración</button>
        </form>
    </main>
</body>
</html>
