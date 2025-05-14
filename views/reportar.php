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
    <title>Reportar Noticia - Check News</title>
    <style>
    /* Reset y fuente principal */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        display: flex;
        min-height: 100vh;
        background-color: #f8f9fa;
    }

    /* Barra lateral */
    .sidebar {
        width: 20%;
        max-width: 250px;
        background-color: #2c3e50;
        color: #ecf0f1;
        padding: 2rem 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }

    .logo-container {
        text-align: center;
        margin-bottom: 2rem;
    }

    .logo {
        width: 90px;
        height: 90px;
        object-fit: cover;
        margin-bottom: 1rem;
        border-radius: 50%;
        border: 3px solid #3498db;
    }

    .sidebar h2 {
        font-size: 1.5rem;
        color: #ecf0f1;
        margin-bottom: 2rem;
        font-weight: 600;
    }

    .sidebar ul {
        list-style: none;
        width: 100%;
    }

    .sidebar ul li {
        margin: 1rem 0;
    }

    .sidebar ul li a {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        text-decoration: none;
        color: #bdc3c7;
        font-size: 1rem;
        padding: 0.8rem 1rem;
        border-radius: 0.75rem; /* 2xl */
        transition: all 0.3s ease;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
        background-color: #34495e;
        color: #ecf0f1;
        transform: translateX(5px);
    }

    /* Contenido principal */
    .content,
    .menu-contenido {
        margin-left: 20%;
        width: 80%;
        padding: 2.5rem;
        background-color: #f8f9fa;
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            position: relative;
            height: auto;
        }
        .content, .menu-contenido {
            margin-left: 0;
            width: 100%;
        }
    }

    /* User Info */
    .user-info {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 1rem;
        text-align: right;
        margin-bottom: 2rem;
        font-size: 1rem;
        color: #7f8c8d;
    }
    .user-info .welcome {
        font-weight: 500;
        color: #2c3e50;
    }
    .user-info a {
        color: #3498db;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border: 1px solid #3498db;
        border-radius: 1.25rem; /* 2xl */
        transition: all 0.3s ease;
    }
    .user-info a:hover {
        background-color: #3498db;
        color: white;
    }

    /* Form Container (equivalente a search-container / form-container) */
    .form-container,
    .search-container {
        background-color: white;
        padding: 2rem;
        border-radius: 1.5rem; /* xl */
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }

    .form-title,
    .search-container h2 {
        color: #2c3e50;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    .search-container p {
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    /* Inputs y botones */
    .form-group,
    .search-bar {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-control,
    .search-bar input {
        flex: 1;
        padding: 1rem;
        font-size: 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 0.5rem; /* md */
        transition: all 0.3s ease;
        outline: none;
    }
    .form-control:focus,
    .search-bar input:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52,152,219,0.2);
    }

    .btn-primary,
    .search-bar button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: #3498db;
        color: white;
    }
    .btn-primary:hover,
    .search-bar button:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .btn-primary:active,
    .search-bar button:active {
        transform: translateY(0);
    }

    /* Alertas */
    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 0.5rem;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
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
            Bienvenido, <?php echo $nombre_completo; ?> 
            <a href="logout.php">Cerrar sesión</a>
        </div>

        <div class="form-container">
            <h1 class="form-title">Reportar Noticia Dudosa</h1>

            <form method="POST" action="reportar.php">

                <div class="form-group">
                    <label for="noticia_texto" class="form-label">URL o Texto de la noticia:</label>
                    <input type="noticia_texto" id="unoticia_textor" name="noticia_texto" class="form-control"
                           value="<?php echo isset($_POST['noticia_texto']) ? $_POST['noticia_texto'] : ''; ?>" 
                           placeholder="https://ejemplo.com/noticia" required>
                </div>

                <div class="form-group">
                    <label for="categoria" class="form-label">Categoría:</label>
                    <select id="categoria" name="categoria" class="form-control" required>
                        <option value="">Seleccione una categoría</option>
                        <option value="cancer" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 'cancer') ? 'selected' : ''; ?>>Cancer</option>
                        <option value="diabetes" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 'diabetes') ? 'selected' : ''; ?>>Diabetes</option>
                        <option value="asma" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 'asma') ? 'selected' : ''; ?>>Asma</option>
                        <option value="hipertension" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 'hipertension') ? 'selected' : ''; ?>>Hipertension</option>
                        <option value="obesidad" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 'obesidad') ? 'selected' : ''; ?>>Obesidad</option>
                        <option value="cardiovasculares" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 'cardiovasculares') ? 'selected' : ''; ?>>Enfermedades cardiovasculares</option>
                        <option value="otros" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 'otros') ? 'selected' : ''; ?>>Otros</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="comentario" class="form-label">¿Por qué crees que esta noticia es falsa o dudosa?</label>
                    <textarea id="comentario" name="comentario" class="form-control" required></textarea>
                </div>
                <small class="text-muted">Mínimo 20 caracteres. Describe con detalle tus sospechas.</small>

                <button type="submit" class="btn btn-primary">Reportar Noticia</button>
            </form>
        </div>
    </div>
</body>
</html>