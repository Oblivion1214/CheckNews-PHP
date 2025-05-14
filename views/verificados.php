<?php
include 'config.php';
session_start();

// 1) Verificar sesión
if (!isset($_SESSION['usuarioID'])) {
    header("Location: login.php");
    exit();
}

// 2) Obtener nombre completo
$user_id = $_SESSION['usuarioID'];
$stmt = $connection->prepare("SELECT nombre, apellido_paterno FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}
$user = $res->fetch_assoc();
$nombre_completo = htmlspecialchars($user['nombre'] . ' ' . $user['apellido_paterno'], ENT_QUOTES);
$stmt->close();

// 3) Construir consulta base: solo revisados
$sql  = "SELECT * FROM reportes_noticias_falsas WHERE estatus = 'revisado'";
$types = "";
$params = [];

// 4) Filtro búsqueda en texto o comentario
if (!empty($_GET['q'])) {
    $q = "%{$_GET['q']}%";
    $sql .= " AND (noticia_texto LIKE ? OR comentario LIKE ?)";
    $types .= "ss";
    $params[] = $q;
    $params[] = $q;
}

// 5) Filtro por fecha exacta (fecha_reporte)
if (!empty($_GET['fecha'])) {
    $sql .= " AND DATE(fecha_reporte) = ?";
    $types .= "s";
    $params[] = $_GET['fecha'];
}

// 6) Filtro por categoría
if (!empty($_GET['categoria']) && $_GET['categoria'] !== 'all') {
    $sql .= " AND categoria = ?";
    $types .= "s";
    $params[] = $_GET['categoria'];
}

// 7) Filtro por veracidad (resultado)
if (!empty($_GET['veracidad']) && $_GET['veracidad'] !== 'all') {
    $val = ($_GET['veracidad'] === 'verdadero') ? 'Noticia Verdadera' : 'Noticia Falsa';
    $sql .= " AND resultado = ?";
    $types .= "s";
    $params[] = $val;
}

$sql .= " ORDER BY fecha_reporte DESC";

// 8) Preparar y ejecutar
$stmt = $connection->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Noticias Verificadas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f4f4f9;
        }

        .sidebar {
            width: 20%;
            background-color: #ffffff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-bottom: 0.5rem;
        }

        .sidebar h2 {
            font-size: 1.5rem;
            color: #333;
        }

        .sidebar ul {
            list-style: none;
            width: 100%;
        }

        .sidebar ul li {
            margin: 1rem 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #555;
            font-size: 1rem;
            display: block;
            padding: 0.5rem 0.8rem;
            border-radius: 8px;
            text-align: center;
        }

        .sidebar ul li a:hover {
            background-color: #f0f0f0;
        }

        .content {
            width: 80%;
            padding: 2rem;
        }

        .user-info {
            text-align: right;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
        }

        .user-info a {
            color: #3498db;
            text-decoration: none;
            margin-left: 10px;
        }

        .user-info a:hover {
            text-decoration: underline;
        }

        .search-container {
            margin-bottom: 2rem;
        }

        .search-container input {
            width: 70%;
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-right: 10px;
        }

        .search-container button {
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #2980b9;
        }

        .filters {
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .filters label {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #555;
        }

        .filters input, .filters select {
            padding: 0.8rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 0.9rem;
        }

        .results {
            margin-top: 2rem;
        }

        .results h2 {
            margin-bottom: 1.5rem;
            color: #333;
        }

        .result-item {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .result-item h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .result-item p {
            font-size: 1rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .result-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .result-date {
            color: #888;
        }

        .result-category {
            color: #3498db;
            font-weight: bold;
        }

        .verification-true {
            color: #27ae60;
            font-weight: bold;
        }

        .verification-false {
            color: #e74c3c;
            font-weight: bold;
        }

        .no-results {
            text-align: center;
            padding: 2rem;
            color: #666;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar, .content {
                width: 100%;
            }
            
            .search-container input {
                width: 100%;
                margin-bottom: 10px;
                margin-right: 0;
            }
            
            .search-container button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo-container">
      <img src="CheckNews.png" alt="Logo">
      <h2>CheckNews</h2>
    </div>
    <ul>
      <li><a href="Principal.php">Explorar</a></li>
      <li><a href="verificados.php" class="active">Noticias revisadas</a></li>
      <li><a href="herramientas.php">Herramientas de Ayuda</a></li>
      <li><a href="reportar.php">Reportar Noticia</a></li>
    </ul>
  </div>

  <!-- Contenido principal -->
  <div class="content">
    <div class="user-info">
      Bienvenido, <?php echo $nombre_completo; ?> (<a href="logout.php">Cerrar sesión</a>)
    </div>

    <!-- Filtros y búsqueda -->
    <form method="GET" class="filters">
      <input type="text" name="q" placeholder="Buscar texto o comentario" value="<?php echo htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES); ?>">
      <input type="date" name="fecha" value="<?php echo htmlspecialchars($_GET['fecha'] ?? '', ENT_QUOTES); ?>">
      <select name="categoria">
        <option value="all">Todas categorías</option>
        <?php 
          $cats = ['cancer'=>'Cáncer','diabetes'=>'Diabetes','asma'=>'Asma','hipertension'=>'Hipertensión',
                   'obesidad'=>'Obesidad','cardiovasculares'=>'Cardiovasculares','otros'=>'Otros'];
          foreach($cats as $k=>$label){
            $sel = (($_GET['categoria'] ?? '') === $k) ? 'selected':''; 
            echo "<option value=\"$k\" $sel>$label</option>";
          }
        ?>
      </select>
      <select name="veracidad">
        <option value="all">Todas veracidades</option>
        <option value="verdadero" <?php if(($_GET['veracidad']??'')==='verdadero') echo 'selected'; ?>>Noticia Verdadera</option>
        <option value="falso" <?php if(($_GET['veracidad']??'')==='falso') echo 'selected'; ?>>Noticia Falsa</option>
      </select>
      <button type="submit">Filtrar</button>
    </form>

    <!-- Resultados -->
    <div class="results">
      <?php if($result->num_rows): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="result-item">
            <h3><?php echo htmlspecialchars($row['resultado'], ENT_QUOTES); ?></h3>
            <p><strong>Noticia:</strong> <?php echo nl2br(htmlspecialchars($row['noticia_texto'], ENT_QUOTES)); ?></p>
            <p><strong>Comentario:</strong> <?php echo nl2br(htmlspecialchars($row['comentario'], ENT_QUOTES)); ?></p>
            <div class="result-meta">
              <span class="result-date"><?php echo date('d/m/Y H:i', strtotime($row['fecha_reporte'])); ?></span>
              <span class="result-category"><?php echo htmlspecialchars(ucfirst($row['categoria']), ENT_QUOTES); ?></span>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-results">No hay noticias revisadas con esos criterios.</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>