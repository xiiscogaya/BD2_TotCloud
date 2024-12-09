<?php
session_start();
include '../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de PaaS
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: modificar_paas.php');
    exit;
}

$idPaaS = intval($_GET['id']);

// Obtener los datos de la configuración PaaS
$query_paas = "SELECT * FROM paas WHERE idPaaS = ?";
$stmt = $conn->prepare($query_paas);
$stmt->bind_param('i', $idPaaS);
$stmt->execute();
$result_paas = $stmt->get_result();
$paas = $result_paas->fetch_assoc();

if (!$paas) {
    header('Location: modificar_paas.php');
    exit;
}

// Obtener componentes asociados
$cpus_seleccionadas = $conn->query("SELECT * FROM r_paas_cpu WHERE idPaaS = $idPaaS");
$rams_seleccionadas = $conn->query("SELECT * FROM r_paas_ram WHERE idPaaS = $idPaaS");
$almacenamientos_seleccionados = $conn->query("SELECT * FROM r_paas_almacenamiento WHERE idPaaS = $idPaaS");

// Obtener las opciones disponibles
$cpus = $conn->query("SELECT * FROM cpu");
$rams = $conn->query("SELECT * FROM ram");
$almacenamientos = $conn->query("SELECT * FROM almacenamiento");
$ips = $conn->query("SELECT * FROM direccionip WHERE idPaaS IS NULL OR idPaaS = $idPaaS"); // IP actual o libres
$sos = $conn->query("SELECT * FROM sistemaoperativo");

// Manejar el envío del formulario
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $idSO = intval($_POST['idSO']);
    $idIp = intval($_POST['idIp']);

    $cpus = $_POST['cpus'] ?? [];
    $rams = $_POST['rams'] ?? [];
    $almacenamientos = $_POST['almacenamientos'] ?? [];

    if (empty($nombre) || $idSO <= 0 || $idIp <= 0) {
        $message = 'Todos los campos son obligatorios.';
    } else {
        // Actualizar configuración PaaS
        $update_query = "UPDATE paas SET Nombre = ?, idSO = ? WHERE idPaaS = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param('sii', $nombre, $idSO, $idPaaS);

        if ($stmt_update->execute()) {
            // Limpiar componentes asociados
            $conn->query("DELETE FROM r_paas_cpu WHERE idPaaS = $idPaaS");
            $conn->query("DELETE FROM r_paas_ram WHERE idPaaS = $idPaaS");
            $conn->query("DELETE FROM r_paas_almacenamiento WHERE idPaaS = $idPaaS");

            // Asociar IP a esta configuración
            $conn->query("UPDATE direccionip SET idPaaS = NULL WHERE idPaaS = $idPaaS");
            $conn->query("UPDATE direccionip SET idPaaS = $idPaaS WHERE idIp = $idIp");

            // Insertar componentes seleccionados
            $insert_component = function($table, $idPaaS, $component_column, $component_id, $cantidad) use ($conn) {
                $query = "INSERT INTO $table (idPaaS, $component_column, Cantidad) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('iii', $idPaaS, $component_id, $cantidad);
                $stmt->execute();
            };

            foreach ($cpus as $cpu) {
                if (!empty($_POST["cantidad_cpu_$cpu"])) {
                    $cantidad = intval($_POST["cantidad_cpu_$cpu"]);
                    $insert_component('r_paas_cpu', $idPaaS, 'idCPU', $cpu, $cantidad);
                }
            }
            foreach ($rams as $ram) {
                if (!empty($_POST["cantidad_ram_$ram"])) {
                    $cantidad = intval($_POST["cantidad_ram_$ram"]);
                    $insert_component('r_paas_ram', $idPaaS, 'idRAM', $ram, $cantidad);
                }
            }
            foreach ($almacenamientos as $almacenamiento) {
                if (!empty($_POST["cantidad_almacenamiento_$almacenamiento"])) {
                    $cantidad = intval($_POST["cantidad_almacenamiento_$almacenamiento"]);
                    $insert_component('r_paas_almacenamiento', $idPaaS, 'idAlmacenamiento', $almacenamiento, $cantidad);
                }
            }

            $message = 'Configuración PaaS actualizada exitosamente.';
        } else {
            $message = 'Error al actualizar la configuración PaaS.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar PaaS - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Editar Configuración PaaS</h1>
    </header>

    <main class="container my-5">
        <!-- Botón de Volver a Lista PaaS -->
        <div class="container my-3">
            <a href="modificar_paas.php" class="btn btn-secondary">Volver</a>
        </div>
        <h2 class="text-center">Modificar Configuración PaaS</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info text-center"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($paas['Nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="idSO" class="form-label">Sistema Operativo</label>
                <select class="form-select" id="idSO" name="idSO" required>
                    <?php while ($row_so = $sos->fetch_assoc()): ?>
                        <option value="<?php echo $row_so['idSO']; ?>" <?php echo $row_so['idSO'] == $paas['idSO'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row_so['Nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Seleccionar CPUs</label>
                <?php while ($row = $cpus->fetch_assoc()): ?>
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="cpus[]" value="<?php echo $row['idCPU']; ?>" 
                               <?php echo in_array($row['idCPU'], array_column($cpus_seleccionadas->fetch_all(MYSQLI_ASSOC), 'idCPU')) ? 'checked' : ''; ?>>
                        <span class="ms-2"><?php echo htmlspecialchars($row['Nombre'] . ' - ' . $row['Nucleos'] . ' núcleos a ' . $row['Frecuencia'] . 'GHz'); ?></span>
                        <input type="number" name="cantidad_cpu_<?php echo $row['idCPU']; ?>" min="1" max="<?php echo $row['Cantidad']; ?>" 
                               value="<?php echo $row['Cantidad']; ?>" class="form-control ms-3" style="max-width: 100px;">
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Seleccionar RAM</label>
                <?php while ($row = $rams->fetch_assoc()): ?>
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="rams[]" value="<?php echo $row['idRAM']; ?>" 
                            <?php echo in_array($row['idRAM'], array_column($rams_seleccionadas->fetch_all(MYSQLI_ASSOC), 'idRAM')) ? 'checked' : ''; ?>>
                        <span class="ms-2">
                            <?php echo htmlspecialchars($row['Nombre'] . ' - ' . $row['Capacidad'] . 'GB a ' . $row['Frecuencia'] . 'MHz (Tipo: ' . $row['Tipo'] . ')'); ?>
                        </span>
                        <input type="number" 
                            name="cantidad_ram_<?php echo $row['idRAM']; ?>" 
                            min="1" 
                            max="<?php echo $row['Cantidad']; ?>" 
                            value="<?php 
                                $cantidad_ram = array_filter($rams_seleccionadas->fetch_all(MYSQLI_ASSOC), function($selected) use ($row) {
                                    return $selected['idRAM'] == $row['idRAM'];
                                });
                                echo $cantidad_ram ? reset($cantidad_ram)['Cantidad'] : '';
                            ?>" 
                            class="form-control ms-3" 
                            style="max-width: 100px;">
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Seleccionar Almacenamientos</label>
                <?php while ($row = $almacenamientos->fetch_assoc()): ?>
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="almacenamientos[]" value="<?php echo $row['idAlmacenamiento']; ?>" 
                            <?php echo in_array($row['idAlmacenamiento'], array_column($almacenamientos_seleccionados->fetch_all(MYSQLI_ASSOC), 'idAlmacenamiento')) ? 'checked' : ''; ?>>
                        <span class="ms-2">
                            <?php echo htmlspecialchars($row['Nombre'] . ' - ' . $row['Capacidad'] . 'GB (' . $row['Tipo'] . ', Lectura: ' . $row['VelocidadLectura'] . 'MB/s, Escritura: ' . $row['VelocidadEscritura'] . 'MB/s)'); ?>
                        </span>
                        <input type="number" 
                            name="cantidad_almacenamiento_<?php echo $row['idAlmacenamiento']; ?>" 
                            min="1" 
                            max="<?php echo $row['Cantidad']; ?>" 
                            value="<?php 
                                $cantidad_almacenamiento = array_filter($almacenamientos_seleccionados->fetch_all(MYSQLI_ASSOC), function($selected) use ($row) {
                                    return $selected['idAlmacenamiento'] == $row['idAlmacenamiento'];
                                });
                                echo $cantidad_almacenamiento ? reset($cantidad_almacenamiento)['Cantidad'] : '';
                            ?>" 
                            class="form-control ms-3" 
                            style="max-width: 100px;">
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Seleccionar IP</label>
                <select class="form-select" name="idIp" required>
                    <option value="">Selecciona una IP</option>
                    <?php while ($row = $ips->fetch_assoc()): ?>
                        <option value="<?php echo $row['idIp']; ?>" 
                                <?php echo $row['idIp'] == $paas['idPaaS'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['Direccion']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Actualizar Configuración</button>
        </form>
    </main>
</body>
</html>
