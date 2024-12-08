<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TotCloud - Gestión de Servicios en la Nube</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="css/estilos.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        main {
            flex: 1;
        }

        footer {
            position: relative;
            bottom: 0;
            width: 100%;
        }

        .hero {
            background-image: url('img/totcloud.jpg'); /* Cambia esta URL por la imagen de TotCloud */
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        }

        .hero h1 {
            font-size: 3rem;
        }

        .hero p {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <?php include '../includes/header.php'; ?>

    <!-- Contenido principal -->
    <main>
        <!-- Sección de bienvenida con la imagen de la empresa -->
        <div class="hero">
            <div class="text-center">
                <h1>Bienvenido a TotCloud</h1>
                <p>Tu solución en la nube para servicios PaaS y SaaS</p>
            </div>
        </div>

        <!-- Descripción de la empresa -->
        <section class="container my-5">
            <h2 class="text-center">¿Quiénes somos?</h2>
            <p class="lead text-center">
                TotCloud es una empresa líder en servicios de infraestructura como servicio (PaaS) y software como servicio (SaaS). 
                Nuestra misión es proporcionar herramientas escalables y eficientes para organizaciones de todos los tamaños, facilitando 
                la gestión de recursos en la nube de forma segura y accesible.
            </p>
        </section>
    </main>

    <!-- Pie de página -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
