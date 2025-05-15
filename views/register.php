<?php
include 'config.php';

$errores = [];
$mensajeExito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombre = $_POST['Nombre'];
    $apellido_paterno = $_POST['ApellidoPaterno'];
    $apellido_materno = $_POST['ApellidoMaterno'];
    $email = $_POST['Email'];
    $contrasena = $_POST['Contrasena'];
    $confirmContrasena = $_POST['ConfirmContrasena'];

    // Validaciones
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido.";
    }

    if ($contrasena !== $confirmContrasena) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    // Verificar si el correo ya está registrado
    $sqlUsuario = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $connection->prepare($sqlUsuario);
    
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $connection->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $errores[] = "El correo electrónico ya está registrado.";
    }

    // Si no hay errores, registrar el usuario
    if (empty($errores)) {
        $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);
        
        $sqlInsert = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, password, tipo_usuario) 
                    VALUES (?, ?, ?, ?, ?, 'normal')";
        $stmt = $connection->prepare($sqlInsert);
        
        if ($stmt === false) {
            die("Error al preparar la consulta: " . $connection->error);
        }
        
        $stmt->bind_param("sssss", $nombre, $apellido_paterno, $apellido_materno, $email, $hashedPassword);

        if ($stmt->execute()) {
            $mensajeExito = "Registro exitoso. ¡Bienvenido!";
            header("Location: Principal.php");
            exit();
        } else {
            $errores[] = "Error al registrar el usuario.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Check News</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .register-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            height: 90vh;
            max-height: 700px;
            z-index: 1;
        }

        .register-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            transform: translateY(0);
            transition: transform 0.5s ease, box-shadow 0.5s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .form-header {
            padding: 30px 30px 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .form-scroll-container {
            flex: 1;
            overflow-y: auto;
            padding: 0 30px 30px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-circle {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 26px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .welcome {
            text-align: center;
            color: #333;
            font-size: 26px;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .section-title {
            color: #555;
            font-size: 16px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .name-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .password-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 40px 15px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }

        .input-group input:focus {
            border-color: #667eea;
        }

        .input-group label {
            position: absolute;
            top: 15px;
            left: 15px;
            color: #999;
            font-size: 14px;
            transition: all 0.3s;
            pointer-events: none;
            background: white;
            padding: 0 5px;
        }

        .input-group.focused label,
        .input-group input:valid + label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #667eea;
        }

        .icon {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #999;
            font-size: 14px;
        }

        .toggle-password {
            position: absolute;
            top: 15px;
            right: 40px;
            color: #999;
            cursor: pointer;
            font-size: 14px;
        }

        .terms {
            margin: 25px 0;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 13px;
            cursor: pointer;
            position: relative;
            padding-left: 30px;
        }

        .checkbox-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #fff;
            border: 2px solid #ddd;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .checkbox-container:hover .checkmark {
            border-color: #667eea;
        }

        .checkbox-container input:checked ~ .checkmark {
            background-color: #667eea;
            border-color: #667eea;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .checkbox-container input:checked ~ .checkmark:after {
            display: block;
        }

        .checkbox-container a {
            color: #667eea;
            text-decoration: none;
        }

        .checkbox-container a:hover {
            text-decoration: underline;
        }

        .register-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .register-btn span {
            position: relative;
            z-index: 1;
            transition: transform 0.3s;
        }

        .register-btn i {
            position: absolute;
            right: 20px;
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s;
        }

        .register-btn:hover {
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .register-btn:hover span {
            transform: translateX(-10px);
        }

        .register-btn:hover i {
            opacity: 1;
            transform: translateX(0);
        }

        .register-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .register-btn:hover::after {
            opacity: 1;
        }

        .login-link {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 20px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .decoration {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            overflow: hidden;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
            animation: float 6s infinite ease-in-out;
        }

        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            left: -50px;
            animation: float 8s infinite ease-in-out 2s;
        }

        .circle-3 {
            width: 150px;
            height: 150px;
            bottom: 100px;
            right: 50px;
            animation: float 5s infinite ease-in-out 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Personalización del scroll */
        .form-scroll-container::-webkit-scrollbar {
            width: 6px;
        }

        .form-scroll-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        .form-scroll-container::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.5);
            border-radius: 10px;
        }

        .form-scroll-container::-webkit-scrollbar-thumb:hover {
            background: rgba(102, 126, 234, 0.7);
        }

        /* Modal de Términos y Condiciones */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            overflow: hidden;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
            max-height: 80vh;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            color: #333;
            font-size: 24px;
            margin: 0;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #333;
        }

        .terms-content {
            flex: 1;
            overflow-y: auto;
            padding-right: 10px;
            color: #555;
            line-height: 1.6;
        }

        .terms-content h3 {
            color: #667eea;
            margin: 20px 0 10px;
            font-size: 18px;
        }

        .terms-content p {
            margin-bottom: 15px;
        }

        .terms-content ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        .terms-content li {
            margin-bottom: 8px;
        }

        /* Scrollbar para el contenido de términos */
        .terms-content::-webkit-scrollbar {
            width: 8px;
        }

        .terms-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .terms-content::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        .terms-content::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        @media (max-width: 768px) {
            .register-container {
                height: 95vh;
                max-height: none;
                margin: 20px;
            }
            
            .name-fields, .password-fields {
                grid-template-columns: 1fr;
            }
            
            .form-header {
                padding: 25px 25px 15px;
            }
            
            .form-scroll-container {
                padding: 0 25px 25px;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .register-box {
                border-radius: 15px;
            }
            
            .logo-circle {
                width: 60px;
                height: 60px;
                font-size: 22px;
            }
            
            .welcome {
                font-size: 22px;
            }
            
            .form-header {
                padding: 20px 20px 10px;
            }
            
            .form-scroll-container {
                padding: 0 20px 20px;
            }

            .modal-header h2 {
                font-size: 20px;
            }

            .terms-content {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="form-header">
                <div class="logo">
                    <div class="logo-circle pulse">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                </div>
                <h1 class="welcome">Crear Cuenta</h1>
                <p class="subtitle">Completa tus datos para registrarte</p>
            </div>
            
            <div class="form-scroll-container">
                <?php if (!empty($errores)): ?>
                    <div style="color: red; margin-bottom: 15px; text-align: center;">
                        <?php foreach ($errores as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($mensajeExito): ?>
                    <div style="color: green; margin-bottom: 15px; text-align: center;">
                        <p><?php echo htmlspecialchars($mensajeExito); ?></p>
                    </div>
                <?php endif; ?>

                <form class="register-form" method="POST" action="">
                    <div class="form-section">
                        <h3 class="section-title">Información Personal</h3>
                        <div class="input-group floating">
                            <input type="text" name="Nombre" id="Nombre" required>
                            <label for="Nombre">Nombre(s)</label>
                            <i class="fas fa-user icon"></i>
                        </div>

                        <div class="name-fields">
                            <div class="input-group floating">
                                <input type="text" name="ApellidoPaterno" id="ApellidoPaterno" required>
                                <label for="ApellidoPaterno">Apellido Paterno</label>
                                <i class="fas fa-user icon"></i>
                            </div>

                            <div class="input-group floating">
                                <input type="text" name="ApellidoMaterno" id="ApellidoMaterno">
                                <label for="ApellidoMaterno">Apellido Materno</label>
                                <i class="fas fa-user icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Datos de Acceso</h3>
                        <div class="input-group floating">
                            <input type="email" name="Email" id="Email" required>
                            <label for="Email">Correo electrónico</label>
                            <i class="fas fa-envelope icon"></i>
                        </div>
                        
                        <div class="password-fields">
                            <div class="input-group floating">
                                <input type="password" name="Contrasena" id="Contrasena" required minlength="8">
                                <label for="Contrasena">Contraseña (mínimo 8 caracteres)</label>
                                <i class="fas fa-lock icon"></i>
                                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                            </div>

                            <div class="input-group floating">
                                <input type="password" name="ConfirmContrasena" id="ConfirmContrasena" required>
                                <label for="ConfirmContrasena">Confirmar contraseña</label>
                                <i class="fas fa-lock icon"></i>
                                <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="terms">
                            <label class="checkbox-container">
                                <input type="checkbox" id="terms" required>
                                <span class="checkmark"></span>
                                Acepto los <a href="#" id="showTerms"> términos y condiciones</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="register-btn">
                            <span>Registrarse</span>
                            <i class="fas fa-user-plus"></i>
                        </button>

                        <p class="login-link">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="decoration">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            <div class="circle circle-3"></div>
        </div>

        <!-- Modal Términos y Condiciones -->
        <div id="termsModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Términos y Condiciones de CheckNews</h2>
                    <span class="close">&times;</span>
                </div>
                <div class="terms-content">
                    <p>Bienvenido a CheckNews, un sistema automatizado diseñado para detectar noticias falsas sobre salud utilizando Machine Learning y Procesamiento del Lenguaje Natural (NLP). Al acceder y utilizar nuestra plataforma web, usted acepta cumplir y estar sujeto a los siguientes términos y condiciones de uso. Si no está de acuerdo con estos términos, no debe utilizar CheckNews.</p>
                    
                    <h3>1. Descripción del Servicio</h3>
                    <p>CheckNews es una herramienta que permite a los usuarios verificar la autenticidad de noticias relacionadas con la salud mediante el análisis automatizado de texto o enlaces. El sistema utiliza algoritmos de Machine Learning y Procesamiento del Lenguaje Natural para clasificar las noticias y consulta una base de datos de noticias previamente verificadas. Los usuarios también pueden consultar el historial de noticias verificadas almacenadas en la base de datos.</p>
                    
                    <h3>2. Limitaciones y Precisión de la Información</h3>
                    <ul>
                        <li>CheckNews se enfoca inicialmente en la detección de noticias falsas sobre temas de salud y principalmente de temas ya existentes.</li>
                        <li>La verificación se basa en modelos de Machine Learning y NLP, cuya efectividad depende de la calidad y diversidad de los datos de entrenamiento.</li>
                        <li>La precisión del sistema puede ser limitada al enfrentarse a noticias completamente nuevas, estructuras de lenguaje no vistas previamente o al intentar identificar sátira o humor.</li>
                        <li>La información proporcionada por CheckNews es el resultado de un análisis automatizado y no debe considerarse como un juicio definitivo o una verdad absoluta. No sustituye el consejo de profesionales de la salud o la verificación manual exhaustiva por parte de expertos.</li>
                        <li>CheckNews puede no ofrecer el mismo nivel de análisis profundo en temas específicos que algunas plataformas de verificación manual.</li>
                    </ul>
                    
                    <h3>3. Conducta del Usuario</h3>
                    <p>Usted se compromete a utilizar CheckNews únicamente para fines lícitos y de acuerdo con estos términos. Usted no debe:</p>
                    <ul>
                        <li>Utilizar la plataforma para difundir información falsa o engañosa intencionalmente.</li>
                        <li>Intentar interferir con el funcionamiento de CheckNews o el análisis de datos.</li>
                        <li>Enviar contenido ilegal, dañino, amenazante, abusivo, acosador, difamatorio, vulgar, obsceno, o racial, étnica o de otra manera objetable.</li>
                        <li>Intentar acceder sin autorización a los sistemas o bases de datos de CheckNews.</li>
                    </ul>
                    
                    <h3>4. Propiedad Intelectual</h3>
                    <p>La plataforma CheckNews, sus algoritmos, bases de datos y contenido (excluyendo el contenido enviado por los usuarios) son propiedad del Instituto Tecnológico Toluca y los creadores del proyecto y están protegidos por las leyes de propiedad intelectual.</p>
                    
                    <h3>5. Descargo de Responsabilidad de Garantías</h3>
                    <p>CheckNews se proporciona "tal cual" y "según disponibilidad" sin garantías de ningún tipo, ya sean expresas o implícitas. No garantizamos que la plataforma será ininterrumpida, libre de errores o completamente segura.</p>
                    
                    <h3>6. Limitación de Responsabilidad</h3>
                    <p>En la medida máxima permitida por la ley, CheckNews y sus creadores no serán responsables de ningún daño directo, indirecto, incidental, especial, consecuente o punitivo que resulte del uso o la imposibilidad de usar la plataforma o de la información obtenida a través de ella. El usuario reconoce que las decisiones basadas en información falsa pueden tener consecuencias peligrosas, y CheckNews es solo una herramienta de apoyo en el proceso de verificación.</p>
                    
                    <h3>7. Privacidad</h3>
                    <p>El manejo de datos en CheckNews cumple con protocolos de seguridad y busca minimizar sesgos en los modelos. Al utilizar la plataforma, usted acepta el tratamiento de sus datos según se describe en nuestra Política de Privacidad (la cual se presentará por separado). Se buscará obtener consentimiento explícito de los usuarios y se cumplirán las normativas vigentes sobre protección de datos.</p>
                    
                    <h3>8. Consideraciones Legales y Éticas</h3>
                    <p>CheckNews opera en un marco legal mexicano que incluye el Derecho de Réplica, la responsabilidad civil por difusión de información falsa y la Ley Olimpia contra la violencia digital. Aunque la plataforma busca combatir la desinformación, el panorama legal mexicano en cuanto a la regulación específica de la inteligencia artificial en ciertos usos aún está en desarrollo. La transparencia en el manejo de la información es fundamental para evitar la censura o discriminación.</p>
                    
                    <h3>9. Modificaciones a los Términos</h3>
                    <p>Nos reservamos el derecho de modificar estos términos y condiciones en cualquier momento. Se le notificará sobre los cambios importantes. Su uso continuado de la plataforma después de dichas modificaciones constituye su aceptación de los nuevos términos.</p>
                    
                    <h3>10. Ley Aplicable</h3>
                    <p>Estos términos y condiciones se rigen e interpretan de acuerdo con las leyes de México.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar/ocultar contraseña
        function setupPasswordToggle(passwordId, toggleId) {
            const toggle = document.getElementById(toggleId);
            const passwordInput = document.getElementById(passwordId);
            
            toggle.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggle.classList.remove('fa-eye');
                    toggle.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggle.classList.remove('fa-eye-slash');
                    toggle.classList.add('fa-eye');
                }
            });
        }
        
        // Configurar los toggles para ambas contraseñas
        setupPasswordToggle('Contrasena', 'togglePassword');
        setupPasswordToggle('ConfirmContrasena', 'toggleConfirmPassword');
        
        // Efecto de etiquetas flotantes
        document.querySelectorAll('.input-group input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentNode.classList.remove('focused');
                }
            });
        });

        // Modal Términos y Condiciones
        const modal = document.getElementById('termsModal');
        const showBtn = document.getElementById('showTerms');
        const closeBtn = document.querySelector('.close');

        showBtn.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });

        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Validación del formulario antes de enviar
        document.querySelector('.register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('Contrasena').value;
            const confirmPassword = document.getElementById('ConfirmContrasena').value;
            const terms = document.getElementById('terms').checked;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                return;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Debes aceptar los términos y condiciones');
                return;
            }
            
            // Mostrar estado de carga
            const btn = this.querySelector('button');
            const originalText = btn.querySelector('span').textContent;
            btn.querySelector('span').textContent = 'Registrando...';
            btn.disabled = true;
        });
    </script>
</body>
</html>