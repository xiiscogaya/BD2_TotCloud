<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de PaaS
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No se ha especificado el PaaS a eliminar.';
    header('Location: ver_organizacion.php');
    exit;
}

$idPaaS = intval($_GET['id']);

// Iniciar transacción para garantizar la integridad de las operaciones
$conn->begin_transaction();
try {
    // Verificar si existen SaaS asociados a este PaaS
    $query_saas_check = "SELECT idSaaS FROM saas WHERE idPaaS = ?";
    $stmt_saas_check = $conn->prepare($query_saas_check);
    $stmt_saas_check->bind_param('i', $idPaaS);
    $stmt_saas_check->execute();
    $result_saas_check = $stmt_saas_check->get_result();

    // Eliminar SaaS asociados
    while ($saas = $result_saas_check->fetch_assoc()) {
        $idSaaS = $saas['idSaaS'];

        // Eliminar relaciones del SaaS con grupos
        $query_delete_saas_group = "DELETE FROM r_saas_grup WHERE idSaaS = ?";
        $stmt_delete_saas_group = $conn->prepare($query_delete_saas_group);
        $stmt_delete_saas_group->bind_param('i', $idSaaS);
        $stmt_delete_saas_group->execute();

        // Eliminar el SaaS
        $query_delete_saas = "DELETE FROM saas WHERE idSaaS = ?";
        $stmt_delete_saas = $conn->prepare($query_delete_saas);
        $stmt_delete_saas->bind_param('i', $idSaaS);
        $stmt_delete_saas->execute();
    }

    // Desvincular el PaaS de los grupos
    $query_delete_paas_group = "DELETE FROM r_paas_grup WHERE idPaaS = ?";
    $stmt_delete_paas_group = $conn->prepare($query_delete_paas_group);
    $stmt_delete_paas_group->bind_param('i', $idPaaS);
    $stmt_delete_paas_group->execute();

    // Actualizar el estado del PaaS a "Disponible" y eliminar cualquier sistema operativo asociado
    $query_update_paas = "UPDATE paas SET Estado = 'Disponible', idSO = NULL WHERE idPaaS = ?";
    $stmt_update_paas = $conn->prepare($query_update_paas);
    $stmt_update_paas->bind_param('i', $idPaaS);
    $stmt_update_paas->execute();

    // Eliminar asociación de direcciones IP con el PaaS
    $query_update_ip = "UPDATE direccionip SET idPaaS = NULL WHERE idPaaS = ?";
    $stmt_update_ip = $conn->prepare($query_update_ip);
    $stmt_update_ip->bind_param('i', $idPaaS);
    $stmt_update_ip->execute();

    // Confirmar la transacción
    $conn->commit();
    $_SESSION['success_message'] = 'El PaaS fue puesto como Disponible y se eliminaron los servicios SaaS asociados.';
    header('Location: ver_organizacion.php');
    exit;
} catch (Exception $e) {
    // Revertir cambios si ocurre un error
    $conn->rollback();
    $_SESSION['error_message'] = 'Error al actualizar el estado del PaaS: ' . $e->getMessage();
    header('Location: ver_organizacion.php');
    exit;
}
?>
