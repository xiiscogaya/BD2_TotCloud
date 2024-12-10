<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

if (!isset($_SESSION['user_id'])) {
    echo "No tienes permisos para realizar esta acción.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idOrg'])) {
    $idOrganizacion = intval($_POST['idOrg']);

    // Verificar si el usuario tiene acceso a esta organización
    $query_check = "SELECT * FROM r_usuario_org WHERE idUsuario = ? AND idOrg = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param('ii', $_SESSION['user_id'], $idOrganizacion);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        echo "No tienes acceso a esta organización.";
        exit;
    }

    // Obtener los usuarios seleccionados
    $usuariosSeleccionados = isset($_POST['usuarios']) ? $_POST['usuarios'] : [];

    // Obtener usuarios actuales de la organización
    $query_actuales = "SELECT idUsuario FROM r_usuario_org WHERE idOrg = ?";
    $stmt_actuales = $conn->prepare($query_actuales);
    $stmt_actuales->bind_param('i', $idOrganizacion);
    $stmt_actuales->execute();
    $result_actuales = $stmt_actuales->get_result();
    $usuariosActuales = [];
    while ($row = $result_actuales->fetch_assoc()) {
        $usuariosActuales[] = $row['idUsuario'];
    }

    // Calcular usuarios a eliminar y añadir
    $usuariosAEliminar = array_diff($usuariosActuales, $usuariosSeleccionados);
    $usuariosAAñadir = array_diff($usuariosSeleccionados, $usuariosActuales);

    // Eliminar usuarios de la organización
    foreach ($usuariosAEliminar as $idUsuario) {
        $query_remove_user = "DELETE FROM r_usuario_org WHERE idUsuario = ? AND idOrg = ?";
        $stmt_remove_user = $conn->prepare($query_remove_user);
        $stmt_remove_user->bind_param('ii', $idUsuario, $idOrganizacion);
        $stmt_remove_user->execute();
    }

    // Añadir usuarios a la organización
    foreach ($usuariosAAñadir as $idUsuario) {
        $query_add_user = "INSERT INTO r_usuario_org (idUsuario, idOrg) VALUES (?, ?)";
        $stmt_add_user = $conn->prepare($query_add_user);
        $stmt_add_user->bind_param('ii', $idUsuario, $idOrganizacion);
        $stmt_add_user->execute();
    }

    echo "Usuarios actualizados correctamente.";
}
