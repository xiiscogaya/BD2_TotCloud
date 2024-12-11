<?php
include '../../../includes/db_connect.php';

$idSaaS = intval($_GET['id'] ?? 0);
$response = [];

if ($idSaaS > 0) {
    // Detalles principales del SaaS
    $query = "
        SELECT 
            s.Nombre, 
            s.Usuario, 
            s.Contraseña, 
            p.Nombre AS PaaS, 
            m.Nombre AS Motor, 
            m.Version AS Version,
            (
                SUM(c.PrecioH * rpc.Cantidad) + 
                SUM(r.PrecioH * rpr.Cantidad) + 
                SUM(a.PrecioH * rpa.Cantidad) + 
                m.PrecioH
            ) AS CosteTotal
        FROM saas s
        JOIN paas p ON s.idPaaS = p.idPaaS
        JOIN motor m ON s.idMotor = m.idMotor
        LEFT JOIN r_paas_cpu rpc ON p.idPaaS = rpc.idPaaS
        LEFT JOIN cpu c ON rpc.idCPU = c.idCPU
        LEFT JOIN r_paas_ram rpr ON p.idPaaS = rpr.idPaaS
        LEFT JOIN ram r ON rpr.idRAM = r.idRAM
        LEFT JOIN r_paas_almacenamiento rpa ON p.idPaaS = rpa.idPaaS
        LEFT JOIN almacenamiento a ON rpa.idAlmacenamiento = a.idAlmacenamiento
        WHERE s.idSaaS = ?
        GROUP BY s.idSaaS";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $idSaaS);
    $stmt->execute();
    $result = $stmt->get_result();
    $response['details'] = $result->fetch_assoc();

    // Componentes asociados al PaaS del SaaS
    $componentesQuery = "
        SELECT 'CPU' AS Tipo, c.Nombre, rpc.Cantidad
        FROM r_paas_cpu rpc
        JOIN cpu c ON rpc.idCPU = c.idCPU
        JOIN paas p ON rpc.idPaaS = p.idPaaS
        WHERE p.idPaaS = (SELECT idPaaS FROM saas WHERE idSaaS = ?)
        UNION ALL
        SELECT 'RAM', r.Nombre, rpr.Cantidad
        FROM r_paas_ram rpr
        JOIN ram r ON rpr.idRAM = r.idRAM
        JOIN paas p ON rpr.idPaaS = p.idPaaS
        WHERE p.idPaaS = (SELECT idPaaS FROM saas WHERE idSaaS = ?)
        UNION ALL
        SELECT 'Almacenamiento', a.Nombre, rpa.Cantidad
        FROM r_paas_almacenamiento rpa
        JOIN almacenamiento a ON rpa.idAlmacenamiento = a.idAlmacenamiento
        JOIN paas p ON rpa.idPaaS = p.idPaaS
        WHERE p.idPaaS = (SELECT idPaaS FROM saas WHERE idSaaS = ?)";

    $stmt = $conn->prepare($componentesQuery);
    $stmt->bind_param('iii', $idSaaS, $idSaaS, $idSaaS);
    $stmt->execute();
    $components = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $response['components'] = $components;
}

header('Content-Type: application/json');
echo json_encode($response);
