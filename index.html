<?php
session_start();

// Configuración reCAPTCHA
define('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
define('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');

// Si ya está logueado, redirigir
if (isset($_SESSION['ciudadano_dni'])) {
    // Verificar si ya votó
    if (isset($_SESSION['ha_votado']) && $_SESSION['ha_votado'] == 1) {
        header("Location: confirmacion_voto.php");
    } else {
        header("Location: cedula_votacion.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elecciones Generales Perú 2026 - ONPE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .onpe-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .onpe-header {
            background: linear-gradient(135deg, #DC143C 0%, #B22222 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .onpe-logo {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .onpe-logo i {
            font-size: 60px;
            color: #DC143C;
        }
        
        .onpe-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .onpe-header p {
            font-size: 14px;
            opacity: 0.95;
            margin: 0;
        }
        
        .onpe-body {
            padding: 40px 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .dni-input {
            height: 60px;
            font-size: 28px;
            text-align: center;
            letter-spacing: 4px;
            font-weight: 700;
            border: 3px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .dni-input:focus {
            border-color: #DC143C;
            box-shadow: 0 0 0 0.25rem rgba(220, 20, 60, 0.25);
        }
        
        .btn-votar {
            height: 60px;
            font-size: 18px;
            font-weight: 700;
            background: linear-gradient(135deg, #DC143C 0%, #B22222 100%);
            border: none;
            border-radius: 10px;
            color: white;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-votar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 20, 60, 0.4);
            background: linear-gradient(135deg, #FF1744 0%, #DC143C 100%);
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 25px;
            border-left: 4px solid #DC143C;
        }
        
        .info-box h6 {
            color: #DC143C;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            margin: 0;
            padding-left: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .badge-year {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
        }

        .footer-text {
            text-align: center;
            color: white;
            margin-top: 20px;
            font-size: 13px;
        }

        .btn-resultados {
            background: rgba(255,255,255,0.2);
            border: 2px solid white;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .btn-resultados:hover {
            background: white;
            color: #DC143C;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="onpe-card">
                <div class="onpe-header">
                    <div class="onpe-logo">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h1>ELECCIONES GENERALES</h1>
                    <p>Oficina Nacional de Procesos Electorales</p>
                    <span class="badge-year">PERÚ 2026</span>
                </div>
                
                <div class="onpe-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-custom mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php
                            switch($_GET['error']) {
                                case 'dni_invalido':
                                    echo '<strong>DNI inválido.</strong> El DNI debe tener 8 dígitos numéricos.';
                                    break;
                                case 'no_encontrado':
                                    echo '<strong>DNI no encontrado.</strong> No estás registrado en el padrón electoral.';
                                    break;
                                case 'ya_voto':
                                    echo '<strong>Ya emitiste tu voto.</strong> Solo puedes votar una vez.';
                                    break;
                                case 'inactivo':
                                    echo '<strong>Ciudadano inactivo.</strong> Tu registro no está habilitado.';
                                    break;
                                case 'captcha_requerido':
                                    echo '<strong>CAPTCHA requerido.</strong> Por favor completa la verificación de seguridad.';
                                    break;
                                case 'captcha_invalido':
                                    echo '<strong>CAPTCHA inválido.</strong> Verifica que no eres un robot e intenta nuevamente.';
                                    break;
                                default:
                                    echo '<strong>Error.</strong> Por favor intenta nuevamente.';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['logout'])): ?>
                        <div class="alert alert-success alert-custom mb-4">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Sesión cerrada correctamente.</strong> Gracias por participar.
                        </div>
                    <?php endif; ?>
                    
                    <form action="login_electoral.php" method="POST" id="loginForm">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-id-card me-2"></i>
                                Ingresa tu Documento Nacional de Identidad
                            </label>
                            <input type="text" 
                                   class="form-control dni-input" 
                                   name="dni" 
                                   id="dniInput"
                                   placeholder="00000000" 
                                   maxlength="8" 
                                   pattern="[0-9]{8}"
                                   required 
                                   autofocus>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Ingresa solo números, sin espacios ni guiones
                            </small>
                        </div>
                        
                        <!-- reCAPTCHA -->
                        <div class="mb-4 d-flex justify-content-center">
                            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-votar w-100">
                            <i class="fas fa-hand-point-up me-2"></i>
                            INGRESAR A VOTAR
                        </button>
                    </form>
                    
                    <div class="info-box">
                        <h6><i class="fas fa-shield-alt me-2"></i>Información Importante</h6>
                        <ul>
                            <li>Tu voto es secreto y seguro</li>
                            <li>Solo puedes votar una vez</li>
                            <li>Tienes derecho a verificar tu voto</li>
                            <li>El proceso toma menos de 2 minutos</li>
                        </ul>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="resultados_publicos.php" class="btn btn-outline-secondary">
                            <i class="fas fa-chart-bar me-2"></i>
                            Ver Resultados en Tiempo Real
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <a href="admin/" class="text-muted" style="font-size: 12px;">
                            <i class="fas fa-lock me-1"></i>
                            Acceso Administradores
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-text">
                <i class="fas fa-lock me-2"></i>
                Sistema seguro y cifrado • Simulación Electoral 2026
                <br>
                <a href="resultados_publicos.php" class="btn-resultados">
                    <i class="fas fa-poll me-2"></i>
                    Ver Dashboard de Resultados
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Permitir solo números en el input DNI
        document.getElementById('dniInput').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Validación antes de enviar
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const dni = document.getElementById('dniInput').value;
            if (dni.length !== 8) {
                e.preventDefault();
                alert('⚠️ El DNI debe tener exactamente 8 dígitos');
            }
        });
    </script>

    <!-- Script de reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>
</html>
