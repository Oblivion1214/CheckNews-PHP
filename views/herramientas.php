<?php
include 'config.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuarioID'])) {
    header("Location: login.php");
    exit();
}

// Obtener información del usuario
$user_id = $_SESSION['usuarioID'];
$sql = "SELECT nombre, apellido_paterno FROM usuarios WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $nombre_completo = htmlspecialchars($user['nombre'] . ' ' . $user['apellido_paterno']);
} else {
    // Si no existe el usuario en BD pero tenía sesión, limpiar todo
    session_destroy();
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herramientas de Ayuda - Check News</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Sidebar - Mobile First */
        .sidebar {
            width: 100%;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            order: 2; /* Móvil: sidebar después del contenido */
        }

        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo {
            width: 70px;
            height: 70px;
            object-fit: cover;
            margin-bottom: 0.8rem;
            border-radius: 50%;
            border: 2px solid #3498db;
        }

        .sidebar h2 {
            font-size: 1.2rem;
            color: #ecf0f1;
            font-weight: 600;
        }

        .sidebar ul {
            list-style: none;
            width: 100%;
            margin-top: 1.5rem;
        }

        .sidebar ul li {
            margin: 0.8rem 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #bdc3c7;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            padding: 0.6rem 0.8rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .sidebar ul li a i {
            margin-right: 8px;
            width: 18px;
            text-align: center;
            color: #3498db;
        }

        .sidebar ul li a:hover {
            background-color: #34495e;
            color: #ecf0f1;
            transform: translateX(5px);
        }

        .sidebar ul li a.active {
            background-color: #3498db;
            color: white;
            font-weight: 500;
        }

        /* Main Content */
        .content {
            width: 100%;
            padding: 1.5rem;
            background-color: #f8f9fa;
            order: 1; /* Móvil: contenido primero */
        }

        .user-info {
            text-align: right;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #7f8c8d;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .user-info a {
            color: #3498db;
            text-decoration: none;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            border: 1px solid #3498db;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .user-info a:hover {
            background-color: #3498db;
            color: white;
            text-decoration: none;
        }

        /* Guide Container */
        .guide-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .guide-container h1 {
            color: #2c3e50;
            margin-bottom: 1.2rem;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .guide-section {
            margin-bottom: 2rem;
            padding-bottom: 1.2rem;
            border-bottom: 1px solid #eee;
        }

        .guide-section:last-child {
            border-bottom: none;
        }

        .guide-section h2 {
            color: #3498db;
            margin: 1.2rem 0;
            font-size: 1.2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .guide-section h2 i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        /* Steps */
        .steps-container {
            display: grid;
            gap: 1.2rem;
        }

        .step {
            display: flex;
            background: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .step:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .step-number {
            background-color: #3498db;
            color: white;
            min-width: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .step-content {
            padding: 1.2rem;
            flex: 1;
        }

        .step-content h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .step-content p {
            color: #7f8c8d;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        /* Tip Box */
        .tip-box {
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            border-left: 3px solid #3498db;
            padding: 1.2rem;
            margin: 1.2rem 0;
            border-radius: 0 6px 6px 0;
            position: relative;
        }

        .tip-box::before {
            content: '💡';
            position: absolute;
            top: 0.8rem;
            right: 0.8rem;
            font-size: 1.5rem;
            opacity: 0.1;
        }

        .tip-box strong {
            color: #3498db;
            display: block;
            margin-bottom: 0.4rem;
            font-size: 1rem;
        }

        .tip-box p {
            color: #555;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        /* Resources */
        .resources-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.2rem;
            margin-top: 1.2rem;
        }

        .resource-card {
            background: white;
            border-radius: 6px;
            padding: 1.2rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .resource-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #3498db;
        }

        .resource-card h3 {
            color: #3498db;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .resource-card a {
            color: #555;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: color 0.3s;
            font-size: 0.9rem;
        }

        .resource-card a:hover {
            color: #2980b9;
        }

        .resource-card a i {
            margin-right: 6px;
            color: #3498db;
        }

        /* Tablet Styles */
        @media (min-width: 768px) {
            body {
                flex-direction: row;
            }
            
            .sidebar {
                width: 35%;
                order: 1;
                padding: 1.5rem 1rem;
            }
            
            .content {
                width: 65%;
                order: 2;
                padding: 2rem;
            }
            
            .user-info {
                justify-content: flex-end;
                font-size: 1rem;
            }
            
            .user-info a {
                padding: 0.5rem 1rem;
                font-size: 1rem;
            }
            
            .guide-container {
                padding: 2rem;
            }
            
            .guide-container h1 {
                font-size: 1.8rem;
            }
            
            .guide-section h2 {
                font-size: 1.3rem;
            }
            
            .resources-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Desktop Styles */
        @media (min-width: 1024px) {
            .sidebar {
                width: 20%;
                padding: 2rem 1rem;
            }
            
            .content {
                width: 80%;
                padding: 2.5rem;
            }
            
            .logo {
                width: 90px;
                height: 90px;
            }
            
            .sidebar h2 {
                font-size: 1.5rem;
            }
            
            .sidebar ul li a {
                font-size: 1rem;
                padding: 0.8rem 1rem;
            }
            
            .guide-container {
                max-width: 900px;
                padding: 2.5rem;
            }
            
            .guide-container h1 {
                font-size: 2rem;
            }
            
            .resources-list {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        /* Small Mobile Adjustments */
        @media (max-width: 480px) {
            .step {
                flex-direction: column;
            }
            
            .step-number {
                width: 100%;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Barra lateral -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="CheckNews.png" alt="Logo">
            <h2>CheckNews</h2>
        </div>
        <ul>
            <li><a href="Principal.php"><i class="fas fa-compass"></i> Explorar</a></li>
            <li><a href="verificados.php"><i class="fas fa-check-circle"></i> Noticias reportadas</a></li>
            <li><a href="herramientas.php"><i class="fas fa-tools"></i> Herramientas de Ayuda</a></li>
            <li><a href="reportar.php"><i class="fas fa-flag"></i> Reportar Noticia</a></li>
        </ul>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <div class="user-info">
            Bienvenido, <strong><?php echo $nombre_completo; ?></strong> | 
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </div>

        <div class="guide-container">
            <h1>Centro de Ayuda y Herramientas</h1>
            
            <div class="guide-section">
                <h2><i class="fas fa-search"></i> Cómo Verificar Noticias</h2>
                
                <div class="steps-container">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>Examina la fuente</h3>
                            <p>Verifica la URL del sitio web. Muchos sitios falsos imitan direcciones de medios reales con pequeños cambios.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>Busca el titular</h3>
                            <p>Copia y pega el titular en un buscador. Si es falso, es probable que encuentres verificaciones de otros medios.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>Revisa la fecha</h3>
                            <p>Algunas noticias falsas usan información antigua presentándola como actual.</p>
                        </div>
                    </div>
                </div>
                
                <div class="tip-box">
                    <strong>Consejo profesional</strong>
                    <p>Usa nuestra herramienta de búsqueda en la página principal para verificar si ya hemos analizado esa noticia. Nuestra base de datos se actualiza constantemente con verificaciones de expertos.</p>
                </div>
            </div>
            
            <div class="guide-section">
                <h2><i class="fas fa-flag"></i> Reportar Contenido Dudoso</h2>
                
                <div class="steps-container">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>Encuentra el contenido</h3>
                            <p>Localiza la noticia que deseas reportar y copia su URL exacta.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>Completa el formulario</h3>
                            <p>Ve a la sección "Reportar noticia" y proporciona todos los detalles solicitados.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>Describe tus sospechas</h3>
                            <p>Explica claramente por qué crees que la noticia es falsa o engañosa.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h3>Envía el reporte</h3>
                            <p>Nuestro equipo de verificadores analizará tu reporte en menos de 24 horas.</p>
                        </div>
                    </div>
                </div>
                
                <div class="tip-box">
                    <strong>¿Qué ocurre después?</strong>
                    <p>Si confirmamos que es falsa, la agregaremos a nuestra base de datos verificada y podrás recibir actualizaciones sobre este caso si proporcionaste tu correo electrónico.</p>
                </div>
            </div>
            
            <div class="guide-section">
                <h2><i class="fas fa-external-link-alt"></i> Recursos Adicionales</h2>
                <p>Explora estas herramientas profesionales de verificación de hechos:</p>
                
                <div class="resources-list">
                    <div class="resource-card">
                        <h3>Maldita.es</h3>
                        <a href="https://es.maldita.es/" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Visitar sitio web
                        </a>
                    </div>
                    
                    <div class="resource-card">
                        <h3>Chequeado</h3>
                        <a href="https://chequeado.com/" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Visitar sitio web
                        </a>
                    </div>
                    
                    <div class="resource-card">
                        <h3>FactCheck.org</h3>
                        <a href="https://www.factcheck.org/" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Visitar sitio web
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Efectos de hover y animaciones
        document.addEventListener('DOMContentLoaded', function() {
            const steps = document.querySelectorAll('.step');
            steps.forEach((step, index) => {
                setTimeout(() => {
                    step.style.opacity = '1';
                    step.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>