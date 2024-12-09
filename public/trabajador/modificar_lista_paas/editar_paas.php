<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
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

// Obtener la IP asociada
$query_ip = "SELECT idIp FROM direccionip WHERE idPaaS = ?";
$stmt_ip = $conn->prepare($query_ip);
$stmt_ip->bind_param('i', $idPaaS);
$stmt_ip->execute();
$result_ip = $stmt_ip->get_result();
$current_ip = $result_ip->fetch_assoc()['idIp'] ?? null;

// Obtener componentes usados actualmente por esta instancia
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

// Obtener datos globales de CPU, sumando la cantidad usada por esta instancia
$cpus = [];
$result_cpus = $conn->query("SELECT * FROM cpu");
while($c = $result_cpus->fetch_assoc()) {
    if (isset($cpus_usadas[$c['idCPU']])) {
        // Sumar las ya usadas por esta instancia
        $c['Cantidad'] = $c['Cantidad'] + $cpus_usadas[$c['idCPU']];
    }
    $cpus[] = $c;
}

// RAM
$rams = [];
$result_rams = $conn->query("SELECT * FROM ram");
while($r = $result_rams->fetch_assoc()) {
    if (isset($rams_usadas[$r['idRAM']])) {
        $r['Cantidad'] = $r['Cantidad'] + $rams_usadas[$r['idRAM']];
    }
    $rams[] = $r;
}

// Almacenamiento
$almacenamientos = [];
$result_alm = $conn->query("SELECT * FROM almacenamiento");
while($a = $result_alm->fetch_assoc()) {
    if (isset($alm_usadas[$a['idAlmacenamiento']])) {
        $a['Cantidad'] = $a['Cantidad'] + $alm_usadas[$a['idAlmacenamiento']];
    }
    $almacenamientos[] = $a;
}

// IPs
$ips = [];
$result_ips = $conn->query("SELECT * FROM direccionip WHERE idPaaS IS NULL OR idPaaS = $idPaaS");
while($i = $result_ips->fetch_assoc()) {
    $ips[] = $i;
}

// SO
$sos = [];
$result_sos = $conn->query("SELECT * FROM sistemaoperativo");
while($so = $result_sos->fetch_assoc()) {
    $sos[] = $so;
}

// Manejo del formulario
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $idSO = intval($_POST['idSO']);
    $idIp = intval($_POST['idIp']);

    $cpus_post = $_POST['cpus'] ?? [];
    $rams_post = $_POST['rams'] ?? [];
    $alm_post = $_POST['almacenamientos'] ?? [];

    if (empty($nombre) || $idSO <= 0 || $idIp <= 0) {
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

            // Actualizar PaaS
            $update_query = "UPDATE paas SET Nombre = ?, idSO = ? WHERE idPaaS = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param('sii', $nombre, $idSO, $idPaaS);
            if (!$stmt_update->execute()) {
                throw new Exception('Error al actualizar PaaS');
            }

            // Limpiar componentes asociados
            $conn->query("DELETE FROM r_paas_cpu WHERE idPaaS = $idPaaS");
            $conn->query("DELETE FROM r_paas_ram WHERE idPaaS = $idPaaS");
            $conn->query("DELETE FROM r_paas_almacenamiento WHERE idPaaS = $idPaaS");

            // Desasociar IP antigua y asociar la nueva
            $conn->query("UPDATE direccionip SET idPaaS = NULL WHERE idPaaS = $idPaaS");
            $conn->query("UPDATE direccionip SET idPaaS = $idPaaS WHERE idIp = $idIp");

            // Función para insertar componentes y restar sus cantidades al inventario global
            $insert_component = function ($table, $idPaaS, $component_column, $component_id, $cantidad) use ($conn) {
                $query = "INSERT INTO $table (idPaaS, $component_column, Cantidad) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('iii', $idPaaS, $component_id, $cantidad);
                if(!$stmt->execute()) {
                    throw new Exception("Error al insertar en $table");
                }
            };

            // Aplicar nuevas selecciones y restar sus cantidades
            // CPUs
            foreach ($cpus_post as $cpu_id) {
                if (!empty($_POST["cantidad_cpu_$cpu_id"])) {
                    $cantidad = intval($_POST["cantidad_cpu_$cpu_id"]);
                    $insert_component('r_paas_cpu', $idPaaS, 'idCPU', $cpu_id, $cantidad);
                    $conn->query("UPDATE cpu SET Cantidad = Cantidad - $cantidad WHERE idCPU = $cpu_id");
                }
            }

            // RAM
            foreach ($rams_post as $ram_id) {
                if (!empty($_POST["cantidad_ram_$ram_id"])) {
                    $cantidad = intval($_POST["cantidad_ram_$ram_id"]);
                    $insert_component('r_paas_ram', $idPaaS, 'idRAM', $ram_id, $cantidad);
                    $conn->query("UPDATE ram SET Cantidad = Cantidad - $cantidad WHERE idRAM = $ram_id");
                }
            }

            // Almacenamiento
            foreach ($alm_post as $alm_id) {
                if (!empty($_POST["cantidad_almacenamiento_$alm_id"])) {
                    $cantidad = intval($_POST["cantidad_almacenamiento_$alm_id"]);
                    $insert_component('r_paas_almacenamiento', $idPaaS, 'idAlmacenamiento', $alm_id, $cantidad);
                    $conn->query("UPDATE almacenamiento SET Cantidad = Cantidad - $cantidad WHERE idAlmacenamiento = $alm_id");
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
                    <?php foreach ($sos as $row_so): ?>
                        <option value="<?php echo $row_so['idSO']; ?>" <?php echo $row_so['idSO'] == $paas['idSO'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row_so['Nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar IP</label>
                <select class="form-select" name="idIp" required>
                    <?php foreach ($ips as $row_ip): ?>
                        <option value="<?php echo $row_ip['idIp']; ?>" <?php echo $row_ip['idIp'] == $current_ip ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row_ip['Direccion']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Seleccionar CPUs</label>
                <?php foreach ($cpus as $cpu): ?>
                    <?php $idCPU = $cpu['idCPU']; ?>
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="cpus[]" value="<?php echo $idCPU; ?>" 
                            <?php echo isset($cpus_usadas[$idCPU]) ? 'checked' : ''; ?>>
                        <span class="ms-2">
                            <?php echo htmlspecialchars($cpu['Nombre'] . ' - ' . $cpu['Nucleos'] . ' núcleos a ' . $cpu['Frecuencia'] . 'GHz'); ?>
                            <strong class="text-muted ms-2">(MAX = <?php echo $cpu['Cantidad']; ?>)</strong>
                        </span>
                        <input type="number" 
                            name="cantidad_cpu_<?php echo $idCPU; ?>" 
                            min="1" 
                            max="<?php echo $cpu['Cantidad']; ?>" 
                            value="<?php echo isset($cpus_usadas[$idCPU]) ? $cpus_usadas[$idCPU] : ''; ?>"
                            class="form-control ms-3" 
                            style="max-width: 100px;">
                    </div>
                <?php endforeach; ?>
            </div>


            <div class="mb-3">
                <label class="form-label">Seleccionar RAM</label>
                <?php foreach ($rams as $ram): ?>
                    <?php $idRAM = $ram['idRAM']; ?>
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="rams[]" value="<?php echo $idRAM; ?>" 
                            <?php echo isset($rams_usadas[$idRAM]) ? 'checked' : ''; ?>>
                        <span class="ms-2">
                            <?php echo htmlspecialchars($ram['Nombre'] . ' - ' . $ram['Capacidad'] . 'GB a ' . $ram['Frecuencia'] . 'MHz (Tipo: ' . $ram['Tipo'] . ')'); ?>
                            <strong class="text-muted ms-2">(MAX = <?php echo $ram['Cantidad']; ?>)</strong>
                        </span>
                        <input type="number" 
                            name="cantidad_ram_<?php echo $idRAM; ?>" 
                            min="1" 
                            max="<?php echo $ram['Cantidad']; ?>" 
                            value="<?php echo isset($rams_usadas[$idRAM]) ? $rams_usadas[$idRAM] : ''; ?>"
                            class="form-control ms-3" 
                            style="max-width: 100px;">
                    </div>
                <?php endforeach; ?>
            </div>


            <div class="mb-3">
            <label class="form-label">Seleccionar Almacenamientos</label>
            <?php foreach ($almacenamientos as $alm): ?>
                <?php $idAlm = $alm['idAlmacenamiento']; ?>
                <div class="d-flex align-items-center mb-2">
                    <input type="checkbox" name="almacenamientos[]" value="<?php echo $idAlm; ?>" 
                        <?php echo isset($alm_usadas[$idAlm]) ? 'checked' : ''; ?>>
                    <span class="ms-2">
                        <?php echo htmlspecialchars($alm['Nombre'] . ' - ' . $alm['Capacidad'] . 'GB (' . $alm['Tipo'] . ', Lectura: ' . $alm['VelocidadLectura'] . 'MB/s, Escritura: ' . $alm['VelocidadEscritura'] . 'MB/s)'); ?>
                        <strong class="text-muted ms-2">(MAX = <?php echo $alm['Cantidad']; ?>)</strong>
                    </span>
                    <input type="number" 
                        name="cantidad_almacenamiento_<?php echo $idAlm; ?>" 
                        min="1" 
                        max="<?php echo $alm['Cantidad']; ?>" 
                        value="<?php echo isset($alm_usadas[$idAlm]) ? $alm_usadas[$idAlm] : ''; ?>"
                        class="form-control ms-3" 
                        style="max-width: 100px;">
                </div>
            <?php endforeach; ?>
        </div>


            <button type="submit" class="btn btn-primary w-100">Actualizar Configuración</button>
        </form>
    </main>
</body>
</html>
