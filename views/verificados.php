<?php
include 'config.php';
session_start();

// 1) Verificar sesión
if (!isset($_SESSION['usuarioID'])) {
    header("Location: login.php");
    exit();
}

// 2) Obtener nombre completo del usuario actual
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

// 3) Construir consulta base con JOIN para traer nombre del reportero
$sql  = "
  SELECT r.*, u.nombre, u.apellido_paterno
    FROM reportes_noticias_falsas AS r
    JOIN usuarios AS u ON u.id = r.usuario_id
  WHERE r.estatus = 'revisado'
";
$types = "";
$params = [];

// 4) Filtros GET
if (!empty($_GET['q'])) {
    $q = "%{$_GET['q']}%";
    $sql .= " AND r.noticia_texto LIKE ? ";
    $types .= "s";
    $params[] = $q;
}
if (!empty($_GET['fecha'])) {
    $sql .= " AND DATE(r.fecha_reporte) = ? ";
    $types .= "s";
    $params[] = $_GET['fecha'];
}
if (!empty($_GET['categoria']) && $_GET['categoria'] !== 'all') {
    $sql .= " AND r.categoria = ? ";
    $types .= "s";
    $params[] = $_GET['categoria'];
}
if (!empty($_GET['veracidad']) && $_GET['veracidad'] !== 'all') {
    $val = ($_GET['veracidad']==='verdadero') ? 'Noticia Verdadera' : 'Noticia Falsa';
    $sql .= " AND r.resultado = ? ";
    $types .= "s";
    $params[] = $val;
}

$sql .= " ORDER BY r.fecha_reporte DESC";

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
  <title>Noticias Revisadas - CheckNews</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
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
        background: #f8f9fa; 
    }

    /* Sidebar - Mobile First */
    .sidebar {
        width: 100%; 
        background: #2c3e50; 
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
        padding: 0.6rem 0.8rem;
        border-radius: 6px; 
        transition: all 0.3s;
        display: flex; 
        align-items: center; 
        gap: 0.6rem;
    }
    
    .sidebar ul li a:hover { 
        background: #34495e; 
        color: #ecf0f1; 
        transform: translateX(5px); 
    }
    
    .sidebar ul li a.active { 
        background: #3498db; 
        color: white; 
    }

    /* Main Content */
    .menu-contenido {
        width: 100%; 
        padding: 1.5rem; 
        background: #f8f9fa;
        order: 1; /* Móvil: contenido primero */
    }
    
    .user-info {
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        gap: 0.8rem;
        text-align: right; 
        margin-bottom: 1.5rem; 
        font-size: 0.9rem; 
        color: #7f8c8d;
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
        border: 1px solid #3498db; 
        border-radius: 20px;
        transition: all 0.3s;
        font-size: 0.85rem;
    }
    
    .user-info a:hover { 
        background: #3498db; 
        color: white; 
    }

    /* Filters */
    .filters {
        display: flex; 
        flex-direction: column;
        gap: 0.8rem;
        margin-bottom: 1.5rem; 
        background: #fff; 
        padding: 1.2rem;
        border-radius: 10px; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .filters input, 
    .filters select {
        padding: 0.8rem; 
        border: 1px solid #e0e0e0; 
        border-radius: 6px;
        transition: all 0.3s; 
        outline: none;
        width: 100%;
    }
    
    .filters select { 
        background: white; 
    }
    
    .filters input:focus, 
    .filters select:focus {
        border-color: #3498db; 
        box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
    }
    
    .filters button {
        padding: 0.8rem; 
        background: #3498db; 
        color: white;
        border: none; 
        border-radius: 6px; 
        font-weight: 600; 
        cursor: pointer;
        display: flex; 
        align-items: center; 
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s;
        width: 100%;
    }
    
    .filters button:hover { 
        background: #2980b9; 
        transform: translateY(-1px); 
    }

    /* Results */
    .results h2 { 
        margin-bottom: 1.2rem; 
        color: #2c3e50; 
        font-size: 1.3rem;
    }
    
    .result-item {
        background: white; 
        border-radius: 10px; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 1.2rem; 
        margin-bottom: 1.2rem;
    }
    
    .result-item h3 { 
        font-size: 1.1rem; 
        margin-bottom: 0.5rem; 
        color: #2c3e50; 
    }
    
    .result-item p { 
        margin-bottom: 0.5rem; 
        color: #7f8c8d; 
        font-size: 0.9rem;
    }
    
    .result-meta {
        display: flex; 
        flex-wrap: wrap;
        justify-content: space-between; 
        font-size: 0.8rem; 
        color: #7f8c8d;
        margin-top: 1rem;
        gap: 0.5rem;
    }
    
    .result-user { 
        font-style: italic; 
    }
    
    .result-date { }
    
    .result-category { 
        font-weight: 600; 
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
        
        .menu-contenido {
            width: 65%;
            order: 2;
            padding: 2rem;
        }
        
        .filters {
            flex-direction: row;
            flex-wrap: wrap;
        }
        
        .filters input,
        .filters select {
            flex: 1;
            min-width: 150px;
        }
        
        .filters button {
            width: auto;
            padding: 0.8rem 1.5rem;
        }
        
        .user-info {
            justify-content: flex-end;
            font-size: 1rem;
        }
        
        .user-info a {
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
    }

    /* Desktop Styles */
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
        
        .filters {
            padding: 1.5rem;
        }
        
        .filters input,
        .filters select {
            padding: 1rem;
        }
        
        .filters button {
            padding: 1rem 2rem;
        }
        
        .results h2 {
            font-size: 1.5rem;
        }
        
        .result-item {
            padding: 1.5rem;
        }
        
        .result-item h3 {
            font-size: 1.2rem;
        }
        
        .result-meta {
            font-size: 0.9rem;
        }
    }
</style>
</head>
<body>
  <div class="sidebar">
    <div class="logo-container">
      <img src="CheckNews.png" alt="Logo">
      <h2>CheckNews</h2>
    </div>
    <ul>
      <li><a href="Principal.php"><i class="fas fa-compass"></i> Explorar</a></li>
      <li><a href="verificados.php" class="active"><i class="fas fa-check-circle"></i> Noticias revisadas</a></li>
      <li><a href="herramientas.php"><i class="fas fa-tools"></i> Herramientas</a></li>
      <li><a href="reportar.php"><i class="fas fa-flag"></i> Reportar</a></li>
    </ul>
  </div>

  <div class="menu-contenido">
    <div class="user-info">
      <span class="welcome">Bienvenido, <?php echo $nombre_completo; ?></span>
      <a href="logout.php">Cerrar sesión</a>
    </div>

    <form method="GET" class="filters">
      <input type="text" name="q" placeholder="Buscar texto o comentario" value="<?php echo htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES); ?>">
      <input type="date" name="fecha" value="<?php echo htmlspecialchars($_GET['fecha'] ?? '', ENT_QUOTES); ?>">
      <select name="categoria">
        <option value="all">Todas categorías</option>
        <?php foreach (['cancer'=>'Cáncer','diabetes'=>'Diabetes','asma'=>'Asma','hipertension'=>'Hipertensión','obesidad'=>'Obesidad','cardiovasculares'=>'Cardiovasculares','otros'=>'Otros'] as $k=>$lab): ?>
          <option value="<?php echo $k;?>" <?php if(($_GET['categoria']??'')===$k) echo 'selected'; ?>>
            <?php echo $lab;?>
          </option>
        <?php endforeach;?>
      </select>
      <select name="veracidad">
        <option value="all">Todas veracidades</option>
        <option value="verdadero" <?php if(($_GET['veracidad']??'')==='verdadero') echo 'selected'; ?>>Noticia Verdadera</option>
        <option value="falso"     <?php if(($_GET['veracidad']??'')==='falso')     echo 'selected'; ?>>Noticia Falsa</option>
      </select>
      <button type="submit"><i class="fas fa-search"></i> Filtrar</button>
    </form>

    <div class="results">
      <h2>Resultados encontrados</h2>
      <?php if ($result->num_rows): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="result-item">
            <h3><?php echo htmlspecialchars($row['resultado'], ENT_QUOTES); ?></h3>
            <p><strong>Noticia:</strong> <?php echo nl2br(htmlspecialchars($row['noticia_texto'], ENT_QUOTES)); ?></p>
            <p><strong>Comentario usuario:</strong> <?php echo nl2br(htmlspecialchars($row['comentario'], ENT_QUOTES)); ?></p>
            <div class="result-meta">
              <span class="result-user">
                Reportado por: <?php echo htmlspecialchars($row['nombre'].' '.$row['apellido_paterno'], ENT_QUOTES); ?>
              </span>
              <span class="result-date">
                <?php echo date('d/m/Y H:i', strtotime($row['fecha_reporte'])); ?>
              </span>
              <span class="result-category">
                <?php echo htmlspecialchars(ucfirst($row['categoria']), ENT_QUOTES); ?>
              </span>
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
