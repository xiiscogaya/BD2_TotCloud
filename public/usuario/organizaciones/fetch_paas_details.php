<?php
include '../../../includes/db_connect.php';

$idPaaS = intval($_GET['id'] ?? 0);
$response = [];

if ($idPaaS > 0) {
    // Detalles principales del PaaS
    $query = "
        SELECT p.Nombre, p.Estado, di.Direccion AS IP,
               (SUM(c.PrecioH * rpc.Cantidad) + SUM(r.PrecioH * rpr.Cantidad) + SUM(a.PrecioH * rpa.Cantidad)) AS CosteTotal
        FROM paas p
        LEFT JOIN r_paas_cpu rpc ON p.idPaaS = rpc.idPaaS
        LEFT JOIN cpu c ON rpc.idCPU = c.idCPU
        LEFT JOIN r_paas_ram rpr ON p.idPaaS = rpr.idPaaS
        LEFT JOIN ram r ON rpr.idRAM = r.idRAM
        LEFT JOIN r_paas_almacenamiento rpa ON p.idPaaS = rpa.idPaaS
        LEFT JOIN almacenamiento a ON rpa.idAlmacenamiento = a.idAlmacenamiento
        LEFT JOIN direccionip di ON p.idPaaS = di.idPaaS
        WHERE p.idPaaS = ?
        GROUP BY p.idPaaS";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $idPaaS);
    $stmt->execute();
    $result = $stmt->get_result();
    $response['details'] = $result->fetch_assoc();

    // Componentes asociados
    $componentesQuery = "
        SELECT 'CPU' AS Tipo, c.Nombre, rpc.Cantidad
        FROM r_paas_cpu rpc
        JOIN cpu c ON rpc.idCPU = c.idCPU
        WHERE rpc.idPaaS = ?
        UNION ALL
        SELECT 'RAM', r.Nombre, rpr.Cantidad
        FROM r_paas_ram rpr
        JOIN ram r ON rpr.idRAM = r.idRAM
        WHERE rpr.idPaaS = ?
        UNION ALL
        SELECT 'Almacenamiento', a.Nombre, rpa.Cantidad
        FROM r_paas_almacenamiento rpa
        JOIN almacenamiento a ON rpa.idAlmacenamiento = a.idAlmacenamiento
        WHERE rpa.idPaaS = ?";

    $stmt = $conn->prepare($componentesQuery);
    $stmt->bind_param('iii', $idPaaS, $idPaaS, $idPaaS);
    $stmt->execute();
    $components = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $response['components'] = $components;
}

header('Content-Type: application/json');
echo json_encode($response);
