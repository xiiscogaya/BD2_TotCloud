<?php
function esTrabajador($conn, $user_id) {
    // Consulta para verificar si el usuario es trabajador
    $worker_query = "SELECT * FROM trabajador WHERE idUsuario = ?";
    $stmt_worker = $conn->prepare($worker_query);

    if (!$stmt_worker) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    $stmt_worker->bind_param('i', $user_id);
    $stmt_worker->execute();

    $worker_result = $stmt_worker->get_result();

    // Retorna true si se encontró el usuario, false si no
    return $worker_result->num_rows > 0;
}