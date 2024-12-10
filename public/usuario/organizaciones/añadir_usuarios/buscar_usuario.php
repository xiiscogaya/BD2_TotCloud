<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search = trim($_POST['search']);
    $idOrg = intval($_POST['idOrg']);

    $query = "SELECT u.idUsuario, u.Nombre, u.Email
              FROM usuario u
              WHERE u.Nombre LIKE CONCAT('%', ?, '%')
              AND u.idUsuario NOT IN (SELECT idUsuario FROM r_usuario_org WHERE idOrg = ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $search, $idOrg);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            echo '<div class="card mb-2">';
            echo '<div class="card-body d-flex justify-content-between align-items-center">';
            echo '<div>';
            echo '<h5 class="card-title">' . htmlspecialchars($user['Nombre']) . '</h5>';
            echo '<p class="card-text">' . htmlspecialchars($user['Email']) . '</p>';
            echo '</div>';
            echo '<button class="btn btn-primary" onclick="addUser(' . $user['idUsuario'] . ')">Añadir</button>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p class="text-center text-muted">No se encontraron usuarios.</p>';
    }
}
?>
