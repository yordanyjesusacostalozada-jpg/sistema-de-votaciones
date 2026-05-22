<?php
/**
 * SISTEMA ELECTORAL PERÚ 2026
 * Página de confirmación de voto
 */

session_start();

// Verificar que el ciudadano esté logueado
if (!isset($_SESSION['ciudadano_dni'])) {
    header("Location: index.php");
    exit();
}

// Verificar que haya votado
if (!isset($_SESSION['ha_votado']) || $_SESSION['ha_votado'] != 1) {
    header("Location: cedula_votacion.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voto Confirmado - Elecciones 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .confirmacion-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .confirmacion-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            text-align: center;
            padding: 40px;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: #28a745;
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease;
        }

        .success-icon i {
            font-size: 60px;
            color: white;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        h1 {
            color: #28a745;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: left;
        }

        .info-box p {
            margin-bottom: 10px;
        }

        .btn-ver-resultados {
            background: linear-gradient(135deg, #DC143C 0%, #B22222 100%);
            border: none;
            color: white;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 10px;
            margin: 10px;
            transition: all 0.3s ease;
        }

        .btn-ver-resultados:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(220, 20, 60, 0.4);
        }

        .btn-cerrar-sesion {
            background: #6c757d;
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 8px;
            margin: 10px;
            transition: all 0.3s ease;
        }

        .btn-cerrar-sesion:hover {
            background: #5a6268;
        }

        .mensaje-importante {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmacion-container">
            <div class="confirmacion-card">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>

                <h1>¡TU VOTO HA SIDO REGISTRADO!</h1>
                <p class="lead text-muted">Gracias por ejercer tu derecho democrático</p>

                <div class="info-box">
                    <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Información del Registro</h6>
                    <p><strong>DNI:</strong> <?php echo htmlspecialchars($_SESSION['ciudadano_dni']); ?></p>
                    <p><strong>Ciudadano:</strong> <?php echo htmlspecialchars($_SESSION['ciudadano_nombre']); ?></p>
                    <p><strong>Fecha y Hora:</strong> <?php echo isset($_SESSION['fecha_voto']) ? date('d/m/Y H:i:s', strtotime($_SESSION['fecha_voto'])) : date('d/m/Y H:i:s'); ?></p>
                    <p class="mb-0"><strong>Estado:</strong> <span class="text-success">✓ Voto Registrado Exitosamente</span></p>
                </div>

                <div class="mensaje-importante">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i><strong>IMPORTANTE:</strong></h6>
                    <ul class="mb-0 text-start" style="font-size: 14px;">
                        <li>Tu voto ha sido registrado de forma ANÓNIMA y SECRETA</li>
                        <li>No es posible identificar por quién votaste</li>
                        <li>El sistema solo registra que ejerciste tu derecho al voto</li>
                        <li>Los resultados serán publicados por la ONPE al finalizar el proceso electoral</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <a href="logout.php" class="btn btn-ver-resultados">
                        <i class="fas fa-check-circle me-2"></i>
                        Finalizar y Cerrar Sesión
                    </a>
                </div>

                <div class="mt-4">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Sistema Electoral Seguro - ONPE 2026
                    </small>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-white">
                    <i class="fas fa-heart me-2"></i>
                    Gracias por contribuir a la democracia del Perú
                </p>
            </div>
        </div>
    </div>

    <script>
        // Prevenir regreso con el botón atrás
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>
</html>
