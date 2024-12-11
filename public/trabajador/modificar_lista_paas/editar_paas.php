<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos
include '../../../includes/check_worker.php'; // Archivo para verificar si el usuario es trabajador


// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Obtener el ID del usuario
$user_id = $_SESSION['user_id'];

// Verificar si el usuario es trabajador
if (!esTrabajador($conn, $user_id)) {
    // Si no es trabajador, redirigir a la página de usuario
    header('Location: ../../usuario/usuario.php');
    exit;
}


// Verificar ID de PaaS
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: modificar_paas.php');
    exit;
}

$idPaaS = intval($_GET['id']);

// Obtener la configuración PaaS
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

// Obtener la dirección IP actualmente asignada (si existe)
$query_current_ip = "SELECT idIp, Direccion FROM direccionip WHERE idPaaS = ?";
$stmt_current_ip = $conn->prepare($query_current_ip);
$stmt_current_ip->bind_param('i', $idPaaS);
$stmt_current_ip->execute();
$result_current_ip = $stmt_current_ip->get_result();
$current_ip = $result_current_ip->fetch_assoc();

// Obtener direcciones IP disponibles (idPaaS NULL o el mismo PaaS)
$query_available_ips = "SELECT idIp, Direccion FROM direccionip WHERE idPaaS IS NULL OR idPaaS = ?";
$stmt_available_ips = $conn->prepare($query_available_ips);
$stmt_available_ips->bind_param('i', $idPaaS);
$stmt_available_ips->execute();
$result_available_ips = $stmt_available_ips->get_result();

// Obtener componentes usados actualmente
$cpus_usadas = [];
$res_cpus_usadas = $conn->query("SELECT idCPU, Cantidad FROM r_paas_cpu WHERE idPaaS = $idPaaS");
while($row = $res_cpus_usadas->fetch_assoc()) {
    $cpus_usadas[$row['idCPU']] = $row['Cantidad'];
}

$rams_usadas = [];
$res_rams_usadas = $conn->query("SELECT idRAM, Cantidad FROM r_paas_ram WHERE idPaaS = $idPaaS");
while($row = $res_rams_usadas->fetch_assoc()) {
    $rams_usadas[$row['idRAM']] = $row['Cantidad'];
}

$alm_usadas = [];
$res_alm_usadas = $conn->query("SELECT idAlmacenamiento, Cantidad FROM r_paas_almacenamiento WHERE idPaaS = $idPaaS");
while($row = $res_alm_usadas->fetch_assoc()) {
    $alm_usadas[$row['idAlmacenamiento']] = $row['Cantidad'];
}

// Obtener CPU (sumando la cantidad usada a la disponibilidad)
$cpus = [];
$result_cpus = $conn->query("SELECT * FROM cpu");
while($c = $result_cpus->fetch_assoc()) {
    if (isset($cpus_usadas[$c['idCPU']])) {
        $c['Cantidad'] = $c['Cantidad'] + $cpus_usadas[$c['idCPU']];
    }
    $cpus[] = $c;
}

// Obtener RAM
$rams = [];
$result_rams = $conn->query("SELECT * FROM ram");
while($r = $result_rams->fetch_assoc()) {
    if (isset($rams_usadas[$r['idRAM']])) {
        $r['Cantidad'] = $r['Cantidad'] + $rams_usadas[$r['idRAM']];
    }
    $rams[] = $r;
}

// Obtener Almacenamientos
$almacenamientos = [];
$result_alm = $conn->query("SELECT * FROM almacenamiento");
while($a = $result_alm->fetch_assoc()) {
    if (isset($alm_usadas[$a['idAlmacenamiento']])) {
        $a['Cantidad'] = $a['Cantidad'] + $alm_usadas[$a['idAlmacenamiento']];
    }
    $almacenamientos[] = $a;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $idCPU = intval($_POST['idCPU']);
    $cantidadCPU = intval($_POST['cantidad_cpu']);
    $idRAM = intval($_POST['idRAM']);
    $cantidadRAM = intval($_POST['cantidad_ram']);
    $idIp = intval($_POST['idIp']); // Nueva IP seleccionada
    $alm_post = $_POST['almacenamientos'] ?? [];

    if (empty($nombre) || $idCPU <= 0 || $cantidadCPU <= 0 || $idRAM <= 0 || $cantidadRAM <= 0 || $idIp <= 0) {
        $message = 'Todos los campos son obligatorios.';
    } else {
        $conn->begin_transaction();
        try {
            // Devolver las cantidades usadas anteriormente al inventario global antes de actualizar
            foreach($cpus_usadas as $cpu_id => $cant) {
                $conn->query("UPDATE cpu SET Cantidad = Cantidad + $cant WHERE idCPU = $cpu_id");
            }
            foreach($rams_usadas as $ram_id => $cant) {
                $conn->query("UPDATE ram SET Cantidad = Cantidad + $cant WHERE idRAM = $ram_id");
            }
            foreach($alm_usadas as $alm_id => $cant) {
                $conn->query("UPDATE almacenamiento SET Cantidad = Cantidad + $cant WHERE idAlmacenamiento = $alm_id");
            }

            // Actualizar el nombre del PaaS
            $update_query = "UPDATE paas SET Nombre = ? WHERE idPaaS = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param('si', $nombre, $idPaaS);
            if (!$stmt_update->execute()) {
                throw new Exception('Error al actualizar PaaS');
            }

            // Actualizar la IP (liberar la antigua y asignar la nueva)
            $conn->query("UPDATE direccionip SET idPaaS = NULL WHERE idPaaS = $idPaaS");
            $query_update_ip = "UPDATE direccionip SET idPaaS = ? WHERE idIp = ?";
            $stmt_update_ip = $conn->prepare($query_update_ip);
            $stmt_update_ip->bind_param('ii', $idPaaS, $idIp);
            if (!$stmt_update_ip->execute()) {
                throw new Exception('Error al actualizar la dirección IP');
            }

            // Limpiar componentes asociados
            $conn->query("DELETE FROM r_paas_cpu WHERE idPaaS = $idPaaS");
            $conn->query("DELETE FROM r_paas_ram WHERE idPaaS = $idPaaS");
            $conn->query("DELETE FROM r_paas_almacenamiento WHERE idPaaS = $idPaaS");

            // Función para insertar componentes y restar sus cantidades al inventario global
            $insert_component = function ($table, $idPaaS, $component_column, $component_id, $cantidad) use ($conn) {
                $query = "INSERT INTO $table (idPaaS, $component_column, Cantidad) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('iii', $idPaaS, $component_id, $cantidad);
                if(!$stmt->execute()) {
                    throw new Exception("Error al insertar en $table");
                }
                // Restar del inventario global
                $update_stock_query = "";
                switch($table) {
                    case 'r_paas_cpu':
                        $update_stock_query = "UPDATE cpu SET Cantidad = Cantidad - ? WHERE idCPU = ?";
                        break;
                    case 'r_paas_ram':
                        $update_stock_query = "UPDATE ram SET Cantidad = Cantidad - ? WHERE idRAM = ?";
                        break;
                    case 'r_paas_almacenamiento':
                        $update_stock_query = "UPDATE almacenamiento SET Cantidad = Cantidad - ? WHERE idAlmacenamiento = ?";
                        break;
                }
                $stmt_upd = $conn->prepare($update_stock_query);
                $stmt_upd->bind_param('ii', $cantidad, $component_id);
                if(!$stmt_upd->execute()) {
                    throw new Exception('Error al actualizar stock');
                }
            };

            // Insertar CPU seleccionada
            $insert_component('r_paas_cpu', $idPaaS, 'idCPU', $idCPU, $cantidadCPU);

            // Insertar RAM seleccionada
            $insert_component('r_paas_ram', $idPaaS, 'idRAM', $idRAM, $cantidadRAM);

            // Insertar Almacenamientos seleccionados
            foreach ($alm_post as $alm_id) {
                if (!empty($_POST["cantidad_almacenamiento_$alm_id"])) {
                    $cantidad = intval($_POST["cantidad_almacenamiento_$alm_id"]);
                    $insert_component('r_paas_almacenamiento', $idPaaS, 'idAlmacenamiento', $alm_id, $cantidad);
                }
            }

            $conn->commit();
            $_SESSION['success_message'] = 'La configuración PaaS se ha actualizado correctamente.';
            header('Location: modificar_paas.php');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $message = 'Error al actualizar la configuración PaaS: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar PaaS - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header class="bg-primary text-white text-center py-3">
    <h1>Editar Configuración PaaS</h1>
</header>
<main class="container my-5">
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
            <label for="idIp" class="form-label">Dirección IP</label>
            <select class="form-select" id="idIp" name="idIp" required>
                <option value="">Selecciona una dirección IP</option>
                <?php
                // Regresar el puntero del resultset al principio
                $result_available_ips->data_seek(0);
                while ($row = $result_available_ips->fetch_assoc()): ?>
                    <option value="<?php echo $row['idIp']; ?>" <?php echo ($current_ip && $row['idIp'] == $current_ip['idIp']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['Direccion']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="idCPU" class="form-label">Seleccionar CPU</label>
            <select class="form-select" id="idCPU" name="idCPU" required>
                <option value="">Selecciona un tipo de CPU</option>
                <?php
                // Regresar al principio del array cpus
                foreach ($cpus as $cpu):
                    $selected = '';
                    $cantidadInicial = '';
                    if (isset($cpus_usadas[$cpu['idCPU']])) {
                        $selected = 'selected';
                        $cantidadInicial = $cpus_usadas[$cpu['idCPU']];
                    }
                ?>
                    <option value="<?php echo $cpu['idCPU']; ?>" data-max="<?php echo $cpu['Cantidad']; ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($cpu['Nombre'] . ' - ' . $cpu['Nucleos'] . ' núcleos a ' . $cpu['Frecuencia'] . 'GHz (Disponible: ' . $cpu['Cantidad'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" class="form-control mt-2" id="cantidadCPU" name="cantidad_cpu" min="1" placeholder="Cantidad de CPU" value="<?php echo $cantidadInicial ?? ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="idRAM" class="form-label">Seleccionar RAM</label>
            <select class="form-select" id="idRAM" name="idRAM" required>
                <option value="">Selecciona un tipo de RAM</option>
                <?php
                $cantidadInicialRAM = '';
                foreach ($rams as $ram):
                    $selected = '';
                    if (isset($rams_usadas[$ram['idRAM']])) {
                        $selected = 'selected';
                        $cantidadInicialRAM = $rams_usadas[$ram['idRAM']];
                    }
                ?>
                    <option value="<?php echo $ram['idRAM']; ?>" data-max="<?php echo $ram['Cantidad']; ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($ram['Nombre'] . ' - ' . $ram['Capacidad'] . 'GB a ' . $ram['Frecuencia'] . 'MHz (Disponible: ' . $ram['Cantidad'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" class="form-control mt-2" id="cantidadRAM" name="cantidad_ram" min="1" placeholder="Cantidad de RAM" value="<?php echo $cantidadInicialRAM ?? ''; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Seleccionar Almacenamientos</label>
            <?php foreach ($almacenamientos as $alm): 
                $idAlm = $alm['idAlmacenamiento'];
                $checked = isset($alm_usadas[$idAlm]) ? 'checked' : '';
                $cantidadAlm = isset($alm_usadas[$idAlm]) ? $alm_usadas[$idAlm] : '';
            ?>
                <div class="d-flex align-items-center mb-2">
                    <input type="checkbox" name="almacenamientos[]" value="<?php echo $idAlm; ?>" <?php echo $checked; ?>>
                    <span class="ms-2">
                        <?php echo htmlspecialchars($alm['Nombre'] . ' - ' . $alm['Capacidad'] . 'GB (' . $alm['Tipo'] . ', Lectura: ' . $alm['VelocidadLectura'] . 'MB/s, Escritura: ' . $alm['VelocidadEscritura'] . 'MB/s)'); ?>
                        <strong class="text-muted ms-2">(MAX = <?php echo $alm['Cantidad']; ?>)</strong>
                    </span>
                    <input type="number" 
                        name="cantidad_almacenamiento_<?php echo $idAlm; ?>" 
                        min="1" 
                        max="<?php echo $alm['Cantidad']; ?>" 
                        value="<?php echo $cantidadAlm; ?>"
                        class="form-control ms-3" 
                        style="max-width: 100px;">
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary w-100">Actualizar Configuración</button>
    </form>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    // Al cambiar la CPU seleccionada, actualizar el max y placeholder
    $('#idCPU').on('change', function () {
        const max = $(this).find(':selected').data('max');
        $('#cantidadCPU').attr('max', max).attr('placeholder', 'Máx: ' + max);
    }).trigger('change'); // Para que se actualice al cargar

    // Al cambiar la RAM seleccionada, actualizar el max y placeholder
    $('#idRAM').on('change', function () {
        const max = $(this).find(':selected').data('max');
        $('#cantidadRAM').attr('max', max).attr('placeholder', 'Máx: ' + max);
    }).trigger('change'); // Para que se actualice al cargar
});
</script>
</body>
</html>
