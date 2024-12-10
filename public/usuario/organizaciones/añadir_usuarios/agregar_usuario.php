<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['userId']);
    $idOrg = intval($_POST['idOrg']);

    // Verificar si ya está agregado
    $check_query = "SELECT * FROM r_usuario_org WHERE idUsuario = ? AND idOrg = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param('ii', $userId, $idOrg);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo 'El usuario ya pertenece a esta organización.';
    } else {
        // Insertar el usuario en la organización
        $insert_query = "INSERT INTO r_usuario_org (idUsuario, idOrg) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param('ii', $userId, $idOrg);

        if ($stmt_insert->execute()) {
            echo 'Usuario añadido correctamente a la organización.';
        } else {
            echo 'Error al añadir el usuario.';
        }
    }
}
?>
