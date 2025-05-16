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
    <title>Página Principal - Check News</title>
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

        /* Barra lateral - Versión móvil primero */
        .sidebar {
            width: 100%;
            background-color: #2c3e50;
            color: white;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            order: 2; /* Para móviles va después del contenido */
        }

        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo-container img {
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
            display: block;
            padding: 0.6rem 0.8rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .sidebar ul li a:hover {
            background-color: #34495e;
            color: #ecf0f1;
            transform: translateX(5px);
        }

        /* Contenido principal */
        .menu-contenido {
            width: 100%;
            padding: 1.5rem;
            background-color: #f8f9fa;
            order: 1; /* Para móviles va primero */
        }

        .user-info {
            text-align: right;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #7f8c8d;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .user-info .welcome {
            font-weight: 500;
            color: #2c3e50;
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
        }

        /* Barra de búsqueda */
        .search-container {
            background-color: white;
            padding: 1.2rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            margin-bottom: 1.5rem;
        }

        .report-button {
            padding: 0.8rem 1.5rem;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .search-bar {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            margin-bottom: 0.8rem;
        }

        .search-bar input {
            width: 100%;
            padding: 0.8rem;
            font-size: 0.9rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            transition: all 0.3s ease;
            outline: none;
        }

        .search-bar input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .search-bar button {
            padding: 0.8rem 1.5rem;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: 100%;
        }

        .search-bar button:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* Resultados */
        .verification-result {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 1.2rem;
            margin-top: 1.2rem;
            display: none;
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
        }

        .result-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .result-icon.real {
            background-color: #2ecc71;
        }

        .result-icon.fake {
            background-color: #e74c3c;
        }

        .result-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .result-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }

        .confidence-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            background-color: #f1f1f1;
            color: #7f8c8d;
            font-size: 0.8rem;
        }

        .confidence-badge.high {
            background-color: #e8f8f5;
            color: #27ae60;
        }

        .confidence-badge.medium {
            background-color: #fef9e7;
            color: #f39c12;
        }

        .confidence-badge.low {
            background-color: #fdedec;
            color: #e74c3c;
        }

        .result-url {
            color: #3498db;
            text-decoration: none;
            word-break: break-all;
            font-size: 0.85rem;
        }

        .extracted-text {
            margin-top: 1.2rem;
        }

        .extracted-text h3 {
            font-size: 1rem;
            color: #2c3e50;
            margin-bottom: 0.6rem;
        }

        .text-content {
            max-height: 120px;
            overflow: hidden;
            transition: max-height 0.3s ease;
            position: relative;
            font-size: 0.9rem;
        }

        .text-content.expanded {
            max-height: none;
        }

        .text-content::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        .text-content.expanded::after {
            opacity: 0;
        }

        .toggle-text {
            color: #3498db;
            cursor: pointer;
            font-weight: 500;
            margin-top: 0.4rem;
            display: inline-block;
            font-size: 0.85rem;
        }

        .confidence-explanation {
            font-size: 0.8rem;
            color: #555;
            font-style: italic;
            margin-top: 0.4rem;
        }

        /* Animación de carga */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
        }

        .spinner {
            width: 30px;
            height: 30px;
            border: 3px solid rgba(52, 152, 219, 0.2);
            border-top-color: #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Media Queries para tablets */
        @media (min-width: 768px) {
            body {
                flex-direction: row;
            }

            .sidebar {
                width: 35%;
                padding: 1.5rem 1rem;
                order: 1;
            }

            .menu-contenido {
                width: 65%;
                padding: 2rem;
                order: 2;
            }

            .search-bar {
                flex-direction: row;
            }

            .search-bar input {
                flex: 1;
            }

            .search-bar button {
                width: auto;
            }
        }

        /* Media Queries para escritorio */
        @media (min-width: 1024px) {
            .sidebar {
                width: 20%;
                padding: 2rem 1rem;
            }

            .menu-contenido {
                width: 80%;
                padding: 2.5rem;
            }

            .logo-container img {
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

            .user-info {
                font-size: 1rem;
                justify-content: flex-end;
            }

            .user-info a {
                padding: 0.5rem 1rem;
                font-size: 1rem;
            }

            .search-container {
                padding: 2rem;
            }

            .search-bar input {
                padding: 1rem;
                font-size: 1rem;
            }

            .search-bar button {
                padding: 1rem 2rem;
                font-size: 1rem;
            }

            .verification-result {
                padding: 2rem;
            }

            .result-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }

            .result-title {
                font-size: 1.5rem;
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
    
    <!-- Contenedor principal -->
    <div class="menu-contenido">
        <!-- Información del usuario -->
        <div class="user-info">
            <span class="welcome">Bienvenido, <?php echo $nombre_completo; ?></span>
            <a href="logout.php">Cerrar sesión</a>
        </div>
        <!-- Barra de búsqueda -->
        <div class="search-container">
            <h2 style="margin-bottom: 1.5rem; color: #2c3e50;">Verificador de Noticias Medicas en Español</h2>
                <p style="color: #7f8c8d; font-size: 0.9rem;">Actualmente el modelo solo cuenta con el soporte de deteccion para las siguientes noticias:
                    Cáncer, Diabetes, Asma, Hipertensión, Obesidad, Enfermedades cardiovasculares
                    
                </p>
            <div class="search-bar">
                <input type="text" id="newsInput" placeholder="Ingrese URL o Texto de la Noticia">
                <button id="verifyButton">
                    <i class="fas fa-search"></i> Verificar
                </button>
            </div>
            <p style="color: #7f8c8d; font-size: 0.9rem;">Pega el enlace de una noticia o escribe directamente el texto que deseas verificar</p>
        </div>
        <!-- Resultados de verificación -->
        <div id="verificationResult" class="verification-result">
            <div class="result-header">
                <div id="resultIcon" class="result-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2 id="resultTitle" class="result-title">Resultado de la Verificación</h2>
            </div>
            
            <div class="result-meta">
                <div id="confidenceBadge" class="confidence-badge">
                    Porcentaje de confianza del modelo: <span id="confidenceValue">0</span>
                </div>
                <p id="confidenceExplanation" class="confidence-explanation" style="display: none;"></p>
            </div>
            
            <div id="resultUrlContainer" style="display: none;">
                <p><strong>URL analizada:</strong> <a id="resultUrl" class="result-url" target="_blank"></a></p>
            </div>
            
            <div id="resultTitleContainer" style="display: none;">
                <p><strong>Título:</strong> <span id="resultNewsTitle"></span></p>
            </div>
            
            <div class="extracted-text">
                <h3>Texto analizado</h3>
                <div id="textContent" class="text-content">
                    <p id="extractedText"></p>
                </div>
                <span id="toggleText" class="toggle-text">Mostrar más</span>
            </div>
            <!-- Boton de reporte -->
            <div id="reportContainer" style="margin-top: 1.5rem; text-align: center;">
                <button id="reportButton" class="report-button" >
                    <i class="fas fa-flag"></i> Reportar Noticia
                </button>
            </div>
        </div>

        <!-- Estado de carga -->
        <div id="loadingState" class="loading" style="display: none;">
            <div class="spinner"></div>
        </div>
        
        <p style="text-align:center; color:#e67e22; margin-top: 2rem; font-size: 0.95rem;">
            <strong>Advertencia:</strong> CheckNews puede cometer errores. Comprueba la información importante antes de tomar decisiones.
        </p>

    </div>

    <!-- dentro de <body>, cerca de #reportContainer -->
    <form id="reportForm" method="POST" action="reportar.php" style="display: none;">
    <input type="hidden" name="noticia_url"     id="rf_url">
    <input type="hidden" name="noticia_titulo"  id="rf_titulo">
    <input type="hidden" name="noticia_texto"   id="rf_texto">
    <input type="hidden" name="resultado"       id="rf_resultado">   <!-- NUEVO -->
    <input type="hidden" name="action"          value="prefill">
    </form>


    <!-- Font Awesome para iconos -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <script>
        document.getElementById('verifyButton').addEventListener('click', function() {
            const input = document.getElementById('newsInput').value.trim();
            const resultContainer = document.getElementById('verificationResult');
            const loadingState = document.getElementById('loadingState');
            
            if (!input) {
                alert('Por favor ingrese una URL o texto de noticia');
                return;
            }

            // Mostrar carga y ocultar resultados anteriores
            loadingState.style.display = 'flex';
            resultContainer.style.display = 'none';

            // Determinar si es URL o texto
            const isUrl = input.startsWith('http://') || input.startsWith('https://');
            const requestData = {
                Noticias: [{
                    IdNoticia: 1,
                    [isUrl ? 'url' : 'Noticia']: input
                }]
            };

            // Enviar a la API Flask
            fetch('https://checknews.onrender.com/predict', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                loadingState.style.display = 'none';
                
                if (data.error) {
                    alert(`Error: ${data.error}`);
                    return;
                }

                const result = data.Resultados[0];
                if (result.error) {
                    alert(`Error: ${result.error}`);
                    return;
                }

                // Procesar resultados
                const isFake = result.Prediccion === 0 || result.prediccion === 0;
                const confidence = result.Confianza || result.confianza;
                const explanation = result.explicacion_confianza || result.explicacion_confianza || '';
                const extractedText = result.texto_extraido || result.Noticia || '';
                const title = result.Titulo || '';
                const url = result.url || '';
                
                // Actualizar UI
                const resultIcon = document.getElementById('resultIcon');
                const resultTitle = document.getElementById('resultTitle');
                const confidenceValue = document.getElementById('confidenceValue');
                const confidenceBadge = document.getElementById('confidenceBadge');
                const extractedTextElement = document.getElementById('extractedText');
                const resultUrl = document.getElementById('resultUrl');
                const resultNewsTitle = document.getElementById('resultNewsTitle');
                const resultUrlContainer = document.getElementById('resultUrlContainer');
                const resultTitleContainer = document.getElementById('resultTitleContainer');

                // Establecer icono y título según el resultado
                if (isFake) {
                    resultIcon.className = 'result-icon fake';
                    resultIcon.innerHTML = '<i class="fas fa-times"></i>';
                    resultTitle.textContent = 'Noticia Falsa';
                    resultTitle.style.color = '#e74c3c';
                } else {
                    resultIcon.className = 'result-icon real';
                    resultIcon.innerHTML = '<i class="fas fa-check"></i>';
                    resultTitle.textContent = 'Noticia Verdadera';
                    resultTitle.style.color = '#2ecc71';
                }

                // Establecer confianza
                confidenceValue.textContent = `${confidence}%`;
                const confidenceExplanation = document.getElementById('confidenceExplanation');
                // Mostrar explicación de confianza
                if (explanation) {
                    confidenceExplanation.textContent = explanation;
                    confidenceExplanation.style.display = 'block';
                } else {
                    confidenceExplanation.style.display = 'none';
                }

                // Mostrar URL si existe
                if (url) {
                    resultUrl.href = url;
                    resultUrl.textContent = url;
                    resultUrlContainer.style.display = 'block';
                } else {
                    resultUrlContainer.style.display = 'none';
                }

                // Mostrar título si existe
                if (title) {
                    resultNewsTitle.textContent = title;
                    resultTitleContainer.style.display = 'block';
                } else {
                    resultTitleContainer.style.display = 'none';
                }

                // Mostrar texto extraído
                extractedTextElement.textContent = extractedText;

                // 1) Preparamos los datos para enviar
                const params = new URLSearchParams();
                // si tienes URL la prefieres, si no el texto
                if (url) {
                params.set('noticia_url', url);
                } 
                if (title) {
                params.set('noticia_titulo', title);
                }
                params.set('noticia_texto', extractedText);
                // 2) Configuramos el botón
                const reportContainer = document.getElementById('reportContainer');
                const reportButton = document.getElementById('reportButton');
                reportButton.onclick = () => {
                // 1) rellenar los campos ocultos
                document.getElementById('rf_url').value    = url;
                document.getElementById('rf_titulo').value = title;
                document.getElementById('rf_texto').value  = extractedText;
                document.getElementById('rf_resultado').value = isFake
                    ? 'Noticia Falsa'
                    : 'Noticia Verdadera';
                // 2) enviar el formulario
                document.getElementById('reportForm').submit();
                };
                
                // Mostrar contenedor de resultados
                resultContainer.style.display = 'block';
            })
            .catch(error => {
                loadingState.style.display = 'none';
                alert(`Error al conectar con el servidor: ${error.message}`);
                console.error('Error:', error);
            });
        });

        // Toggle para expandir/contraer texto
        document.getElementById('toggleText').addEventListener('click', function() {
            const textContent = document.getElementById('textContent');
            const toggleText = document.getElementById('toggleText');
            
            textContent.classList.toggle('expanded');
            
            if (textContent.classList.contains('expanded')) {
                toggleText.textContent = 'Mostrar menos';
            } else {
                toggleText.textContent = 'Mostrar más';
            }
        });
    </script>
</body>
</html>