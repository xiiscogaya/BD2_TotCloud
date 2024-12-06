<?php
// index.php

// Incluimos el encabezado común, que contiene el <head>, la navbar, etc.
// Asegúrate de que la ruta de require_once coincida con la ubicación real de tus archivos.
require_once __DIR__ . '/../includes/header.php'; 
?>

<main class="container my-5">
    <div class="text-center">
        <h2>Bienvenido a la Gestión de Servicios PaaS y SaaS</h2>
        <p class="lead">Seleccione una de las opciones del menú para empezar.</p>
        <!-- Aquí puedes poner una imagen representativa del sistema -->
        <img src="img/placeholder.png" alt="Gestión de Servicios" class="img-fluid rounded">
    </div>
</main>

<?php
// Incluimos el pie de página común, que contiene el cierre del body y el html, así como scripts.
require_once __DIR__ . '/../includes/footer.php'; 
