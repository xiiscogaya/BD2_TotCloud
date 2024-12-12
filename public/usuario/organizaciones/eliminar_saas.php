<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de SaaS
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No se ha especificado el SaaS a eliminar.';
    header('Location: ver_organizacion.php');
    exit;
}

$idSaaS = intval($_GET['id']);

// Iniciar transacción para garantizar la integridad de las operaciones
$conn->begin_transaction();
try {
    // Obtener el PaaS asociado al SaaS
    $query_get_paas = "SELECT idPaaS FROM saas WHERE idSaaS = ?";
    $stmt_get_paas = $conn->prepare($query_get_paas);
    $stmt_get_paas->bind_param('i', $idSaaS);
    $stmt_get_paas->execute();
    $result_get_paas = $stmt_get_paas->get_result();
    $paas = $result_get_paas->fetch_assoc();
    $idPaaS = $paas['idPaaS'] ?? null;

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

    // Si existe un PaaS asociado, actualizar su estado a "Disponible"
    if ($idPaaS !== null) {
        $query_update_paas = "UPDATE paas SET Estado = 'Disponible' WHERE idPaaS = ?";
        $stmt_update_paas = $conn->prepare($query_update_paas);
        $stmt_update_paas->bind_param('i', $idPaaS);
        $stmt_update_paas->execute();
    }

    // Confirmar la transacción
    $conn->commit();
    $_SESSION['success_message'] = 'El SaaS fue eliminado y el PaaS asociado se actualizó a estado Disponible.';
    header('Location: ver_organizacion.php');
    exit;
} catch (Exception $e) {
    // Revertir cambios si ocurre un error
    $conn->rollback();
    $_SESSION['error_message'] = 'Error al eliminar el SaaS: ' . $e->getMessage();
    header('Location: ver_organizacion.php');
    exit;
}
?>
