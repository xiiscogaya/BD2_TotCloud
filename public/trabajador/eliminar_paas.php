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
    $_SESSION['error_message'] = 'No se proporcionó un ID válido para la configuración PaaS.';
    header('Location: modificar_paas.php');
    exit;
}

$idPaaS = intval($_GET['id']);

// Eliminar registros relacionados
try {
    $conn->begin_transaction();

    // Actualizar cantidades de CPU
    $query_cpu = "SELECT idCPU, Cantidad FROM r_paas_cpu WHERE idPaaS = ?";
    $stmt_cpu = $conn->prepare($query_cpu);
    $stmt_cpu->bind_param('i', $idPaaS);
    $stmt_cpu->execute();
    $result_cpu = $stmt_cpu->get_result();
    while ($row = $result_cpu->fetch_assoc()) {
        $update_cpu = "UPDATE cpu SET Cantidad = Cantidad + ? WHERE idCPU = ?";
        $stmt_update_cpu = $conn->prepare($update_cpu);
        $stmt_update_cpu->bind_param('ii', $row['Cantidad'], $row['idCPU']);
        $stmt_update_cpu->execute();
    }

    // Actualizar cantidades de RAM
    $query_ram = "SELECT idRAM, Cantidad FROM r_paas_ram WHERE idPaaS = ?";
    $stmt_ram = $conn->prepare($query_ram);
    $stmt_ram->bind_param('i', $idPaaS);
    $stmt_ram->execute();
    $result_ram = $stmt_ram->get_result();
    while ($row = $result_ram->fetch_assoc()) {
        $update_ram = "UPDATE ram SET Cantidad = Cantidad + ? WHERE idRAM = ?";
        $stmt_update_ram = $conn->prepare($update_ram);
        $stmt_update_ram->bind_param('ii', $row['Cantidad'], $row['idRAM']);
        $stmt_update_ram->execute();
    }

    // Actualizar cantidades de almacenamiento
    $query_storage = "SELECT idAlmacenamiento, Cantidad FROM r_paas_almacenamiento WHERE idPaaS = ?";
    $stmt_storage = $conn->prepare($query_storage);
    $stmt_storage->bind_param('i', $idPaaS);
    $stmt_storage->execute();
    $result_storage = $stmt_storage->get_result();
    while ($row = $result_storage->fetch_assoc()) {
        $update_storage = "UPDATE almacenamiento SET Cantidad = Cantidad + ? WHERE idAlmacenamiento = ?";
        $stmt_update_storage = $conn->prepare($update_storage);
        $stmt_update_storage->bind_param('ii', $row['Cantidad'], $row['idAlmacenamiento']);
        $stmt_update_storage->execute();
    }

    // Desasociar IP de esta configuración PaaS
    $update_ip_query = "UPDATE direccionip SET idPaaS = NULL WHERE idPaaS = ?";
    $stmt_ip = $conn->prepare($update_ip_query);
    $stmt_ip->bind_param('i', $idPaaS);
    $stmt_ip->execute();

    // Eliminar registros de componentes relacionados
    $conn->query("DELETE FROM r_paas_cpu WHERE idPaaS = $idPaaS");
    $conn->query("DELETE FROM r_paas_ram WHERE idPaaS = $idPaaS");
    $conn->query("DELETE FROM r_paas_almacenamiento WHERE idPaaS = $idPaaS");

    // Eliminar la configuración PaaS
    $delete_paas_query = "DELETE FROM paas WHERE idPaaS = ?";
    $stmt_paas = $conn->prepare($delete_paas_query);
    $stmt_paas->bind_param('i', $idPaaS);
    $stmt_paas->execute();

    $conn->commit();

    // Guardar mensaje de éxito en la sesión
    $_SESSION['success_message'] = 'La configuración PaaS se ha eliminado correctamente.';
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = 'Ocurrió un error al intentar eliminar la configuración PaaS.';
}

// Redirigir a la página principal de configuración PaaS
header('Location: modificar_paas.php');
exit;
