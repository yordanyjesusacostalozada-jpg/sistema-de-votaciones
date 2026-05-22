<?php
/**
 * SISTEMA ELECTORAL PERÚ 2026
 * Cédula de Votación Digital
 */

session_start();
include 'conexion.php';

// Verificar que el ciudadano esté logueado
if (!isset($_SESSION['ciudadano_dni'])) {
    header("Location: index.php");
    exit();
}

// Verificar si ya votó
if (isset($_SESSION['ha_votado']) && $_SESSION['ha_votado'] == 1) {
    header("Location: confirmacion_voto.php");
    exit();
}

// Obtener datos de la cédula (partidos y candidatos)
if ($is_production) {
    // PostgreSQL: Usar función
    $query = "SELECT * FROM sp_obtener_cedula()";
    $resultado = pg_query($conexion, $query);
    $partidos = [];
    
    if ($resultado) {
        while ($fila = pg_fetch_assoc($resultado)) {
            // Normalizar nombres de campos para PostgreSQL
            $partidos[] = [
                'partido_id' => $fila['partido_id'],
                'siglas' => $fila['partido_siglas'],
                'nombre_completo' => $fila['partido_nombre'],
                'nombre_corto' => $fila['partido_siglas'],
                'logo_url' => $fila['partido_logo'],
                'color_primario' => $fila['partido_color'],
                'presidente_id' => $fila['candidato_presidente_id'],
                'presidente' => $fila['candidato_presidente_nombres'],
                'presidente_foto' => $fila['candidato_presidente_foto'],
                'presidente_profesion' => $fila['candidato_presidente_profesion'],
                'vp1' => $fila['candidato_vp1_nombres'],
                'vp2' => $fila['candidato_vp2_nombres']
            ];
        }
    }
    pg_close($conexion);
} else {
    // MySQL: Usar procedimiento almacenado
    $query = "CALL sp_obtener_cedula()";
    $resultado = mysqli_query($conexion, $query);
    $partidos = [];
    
    if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $partidos[] = $fila;
        }
    }
    mysqli_close($conexion);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cédula de Votación - Elecciones 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .header-electoral {
            background: linear-gradient(135deg, #DC143C 0%, #B22222 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .votante-info {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .votante-info h5 {
            color: #DC143C;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .cedula-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .cedula-header {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #DC143C 0%, #B22222 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 30px -30px;
        }

        .cedula-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .partido-card {
            border: 3px solid #e0e0e0;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .partido-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .partido-card.selected {
            border-color: #DC143C;
            border-width: 4px;
            background: linear-gradient(135deg, rgba(220, 20, 60, 0.05) 0%, rgba(220, 20, 60, 0.1) 100%);
            box-shadow: 0 10px 30px rgba(220, 20, 60, 0.3);
        }

        .partido-card.selected::before {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 10px;
            background: #DC143C;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }

        .partido-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 15px;
            border-radius: 10px;
            background: #f8f9fa;
            padding: 10px;
        }

        .candidato-foto {
            width: 120px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .partido-info h4 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .candidato-nombre {
            font-size: 18px;
            font-weight: 700;
            color: #DC143C;
            margin-bottom: 5px;
        }

        .btn-confirmar-voto {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 18px 40px;
            font-size: 20px;
            font-weight: 700;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 30px;
        }

        .btn-confirmar-voto:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
        }

        .btn-confirmar-voto:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .btn-confirmar-partido {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 8px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .partido-card.selected .btn-confirmar-partido {
            opacity: 1;
            visibility: visible;
            pointer-events: all;
        }

        .btn-confirmar-partido:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.5);
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        }

        .instrucciones {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .temporizador {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .temporizador i {
            color: #DC143C;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .candidato-foto {
                width: 80px;
                height: 100px;
            }

            .partido-logo {
                width: 60px;
                height: 60px;
            }

            .temporizador {
                position: static;
                margin: 15px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-electoral">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <i class="fas fa-vote-yea fa-3x"></i>
                </div>
                <div class="col-md-8 text-center">
                    <h1 class="mb-0" style="font-size: 28px;">ELECCIONES GENERALES PERÚ 2026</h1>
                    <p class="mb-0">Oficina Nacional de Procesos Electorales - ONPE</p>
                </div>
                <div class="col-md-2 text-end">
                    <a href="logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt me-2"></i>Salir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Temporizador -->
    <div class="temporizador">
        <i class="fas fa-clock"></i>
        <strong>Tiempo transcurrido:</strong> <span id="tiempo">00:00</span>
    </div>

    <div class="container mt-4">
        <!-- Información del Votante -->
        <div class="votante-info">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-user-circle me-2"></i>Información del Votante</h5>
                    <p class="mb-1"><strong>DNI:</strong> <?php echo htmlspecialchars($_SESSION['ciudadano_dni']); ?></p>
                    <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['ciudadano_nombre']); ?></p>
                </div>
                <div class="col-md-6">
                    <h5><i class="fas fa-map-marker-alt me-2"></i>Ubicación</h5>
                    <p class="mb-1"><strong>Departamento:</strong> <?php echo htmlspecialchars($_SESSION['ciudadano_departamento']); ?></p>
                    <p class="mb-1"><strong>Provincia:</strong> <?php echo htmlspecialchars($_SESSION['ciudadano_provincia']); ?></p>
                </div>
            </div>
        </div>

        <!-- Cédula de Votación -->
        <div class="cedula-container">
            <div class="cedula-header">
                <h2><i class="fas fa-vote-yea me-3"></i>CÉDULA DE VOTACIÓN</h2>
                <p class="mb-0">ELECCIÓN PRESIDENCIAL 2026</p>
            </div>

            <div class="instrucciones">
                <h6><i class="fas fa-info-circle me-2"></i><strong>INSTRUCCIONES:</strong></h6>
                <ul class="mb-0">
                    <li>Selecciona SOLO UN candidato presidencial</li>
                    <li>Haz clic sobre la tarjeta del candidato de tu preferencia</li>
                    <li>Revisa tu selección antes de confirmar</li>
                    <li>Una vez confirmado, NO podrás cambiar tu voto</li>
                </ul>
            </div>

            <form id="formVotacion" method="POST" action="procesar_voto.php">
                <input type="hidden" name="partido_id" id="partido_id" value="">
                <input type="hidden" name="tipo_voto" id="tipo_voto" value="VALIDO">
                <input type="hidden" name="tiempo_votacion" id="tiempo_votacion" value="0">

                <div class="row">
                    <?php foreach ($partidos as $partido): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="partido-card" onclick="seleccionarPartido(<?php echo $partido['partido_id']; ?>, '<?php echo htmlspecialchars($partido['siglas']); ?>', this)">>
                            <div class="text-center">
                                <?php
                                    // Mostrar logo sin validar file_exists (para Cloudinary)
                                    $logo_src = !empty($partido['logo_url']) ? $partido['logo_url'] : 'assets/img/partidos/placeholder.svg';
                                ?>
                                <img src="<?php echo htmlspecialchars($logo_src); ?>" 
                                     alt="<?php echo htmlspecialchars($partido['nombre_corto'] ?? $partido['siglas']); ?>" 
                                     class="partido-logo"
                                     onerror="this.onerror=null; this.src='assets/img/partidos/placeholder.svg';">
                                
                                <div class="partido-info">
                                    <h4><?php echo htmlspecialchars($partido['siglas']); ?></h4>
                                    <p class="text-muted mb-3" style="font-size: 13px;">
                                        <?php echo htmlspecialchars($partido['nombre_completo']); ?>
                                    </p>
                                </div>

                                <hr>

                                <div class="candidato-info mt-3">
                                    <?php
                                        // Mostrar foto del presidente sin validar file_exists (para Cloudinary)
                                        $pres_src = !empty($partido['presidente_foto']) ? $partido['presidente_foto'] : 'assets/img/candidatos/placeholder.svg';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($pres_src); ?>" 
                                         alt="<?php echo htmlspecialchars($partido['presidente'] ?? 'Presidente'); ?>" 
                                         class="candidato-foto mb-2"
                                         onerror="this.onerror=null; this.src='assets/img/candidatos/placeholder.svg';">
                                    
                                    <div class="candidato-nombre">
                                        <?php echo htmlspecialchars($partido['presidente'] ?? 'Sin candidato'); ?>
                                    </div>
                                    
                                    <div class="text-muted" style="font-size: 12px;">
                                        <strong>Presidente</strong>
                                    </div>

                                    <?php if (!empty($partido['presidente_profesion'])): ?>
                                    <div class="text-muted mt-2" style="font-size: 11px;">
                                        <em><?php echo htmlspecialchars($partido['presidente_profesion']); ?></em>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($partido['vice1'])): ?>
                                    <div class="mt-3 text-muted" style="font-size: 12px;">
                                        <strong>Vicepresidentes:</strong><br>
                                        1. <?php echo htmlspecialchars($partido['vice1']); ?><br>
                                        <?php if (!empty($partido['vice2'])): ?>
                                        2. <?php echo htmlspecialchars($partido['vice2']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Botón de confirmación dentro de la tarjeta -->
                                <button type="button" class="btn btn-confirmar-partido" onclick="event.stopPropagation(); confirmarVotoPartido(<?php echo $partido['partido_id']; ?>, '<?php echo htmlspecialchars($partido['siglas']); ?>')">
                                    <i class="fas fa-check-double me-2"></i>CONFIRMAR VOTO
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Opción de Voto en Blanco -->
                    <div class="col-12 mb-4">
                        <div class="partido-card" id="voto-blanco" data-tipo="BLANCO" onclick="seleccionarVotoBlanco()">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-file fa-4x text-secondary"></i>
                                </div>
                                <h4 class="text-dark">VOTO EN BLANCO</h4>
                                <p class="text-muted">No deseo votar por ningún partido político. Mi voto no contará para ningún candidato.</p>
                                
                                <button type="button" class="btn btn-confirmar-partido" onclick="event.stopPropagation(); confirmarVotoBlanco()">
                                    <i class="fas fa-check-double me-2"></i>CONFIRMAR VOTO EN BLANCO
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info text-center" id="mensajeSeleccion" style="display: none;">
                    <i class="fas fa-check-circle me-2"></i>
                    Has seleccionado: <strong id="partidoSeleccionado"></strong>
                </div>

                <button type="submit" class="btn btn-confirmar-voto" id="btnConfirmar" disabled>
                    <i class="fas fa-check-double me-3"></i>
                    CONFIRMAR MI VOTO
                </button>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-lock me-1"></i>
                        Tu voto es secreto y anónimo
                    </small>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Confirmación de Voto -->
    <div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 15px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #DC143C 0%, #B22222 100%); color: white; border: none;">
                    <h5 class="modal-title" id="modalConfirmacionLabel">
                        <i class="fas fa-vote-yea me-2"></i>Confirmar Voto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-circle" style="font-size: 60px; color: #ffc107;"></i>
                    </div>
                    <h5 class="mb-3">¿Estás seguro de confirmar tu voto por <strong id="partidoConfirmar" style="color: #DC143C;"></strong>?</h5>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Una vez confirmado, <strong>NO podrás cambiarlo</strong>.
                    </p>
                </div>
                <div class="modal-footer" style="border: none; justify-content: center; padding: 20px;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px;">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn px-4" id="btnConfirmarModal" onclick="procesarVotoConfirmado()" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px;">
                        <i class="fas fa-check-double me-2"></i>Sí, Confirmar Voto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let partidoSeleccionadoId = null;
        let tiempoInicio = Date.now();
        let temporizadorInterval;
        let votoEnviado = false; // Bandera para controlar el envío
        let partidoConfirmarTexto = ''; // Guardar el nombre del partido para el modal
        let origenConfirmacion = ''; // Para saber si viene del botón de tarjeta o general

        // Iniciar temporizador
        temporizadorInterval = setInterval(function() {
            let segundos = Math.floor((Date.now() - tiempoInicio) / 1000);
            let minutos = Math.floor(segundos / 60);
            segundos = segundos % 60;
            
            document.getElementById('tiempo').textContent = 
                String(minutos).padStart(2, '0') + ':' + String(segundos).padStart(2, '0');
            
            document.getElementById('tiempo_votacion').value = Math.floor((Date.now() - tiempoInicio) / 1000);
        }, 1000);

        function seleccionarPartido(partidoId, siglas, element) {
            // Remover selección anterior
            document.querySelectorAll('.partido-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Agregar selección a la tarjeta clickeada
            if (element) {
                element.classList.add('selected');
            }
            
            // Actualizar variables
            partidoSeleccionadoId = partidoId;
            document.getElementById('partido_id').value = partidoId;
            document.getElementById('tipo_voto').value = 'VALIDO';
            
            // Mostrar mensaje de selección
            document.getElementById('mensajeSeleccion').style.display = 'block';
            document.getElementById('partidoSeleccionado').textContent = siglas;
            
            // Habilitar botón de confirmación general
            document.getElementById('btnConfirmar').disabled = false;
        }
        
        function seleccionarVotoBlanco() {
            // Remover selección anterior
            document.querySelectorAll('.partido-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Seleccionar voto en blanco
            const votoBlanco = document.getElementById('voto-blanco');
            if (votoBlanco) {
                votoBlanco.classList.add('selected');
            }
            
            // Actualizar variables
            partidoSeleccionadoId = null;
            document.getElementById('partido_id').value = '';
            document.getElementById('tipo_voto').value = 'BLANCO';
            
            // Mostrar mensaje de selección
            document.getElementById('mensajeSeleccion').style.display = 'block';
            document.getElementById('partidoSeleccionado').textContent = 'Voto en Blanco';
            
            // Habilitar botón de confirmación general
            document.getElementById('btnConfirmar').disabled = false;
        }
        
        function confirmarVotoBlanco() {
            // Remover selección anterior
            document.querySelectorAll('.partido-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Seleccionar voto en blanco
            const votoBlanco = document.getElementById('voto-blanco');
            if (votoBlanco) {
                votoBlanco.classList.add('selected');
            }
            
            // Actualizar variables
            partidoSeleccionadoId = null;
            document.getElementById('partido_id').value = '';
            document.getElementById('tipo_voto').value = 'BLANCO';
            
            // Guardar el nombre para el modal
            partidoConfirmarTexto = 'VOTO EN BLANCO';
            origenConfirmacion = 'tarjeta';
            
            // Mostrar modal de confirmación
            const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
            document.getElementById('partidoConfirmar').textContent = partidoConfirmarTexto;
            modalConfirmacion.show();
        }

        function confirmarVotoPartido(partidoId, siglas) {
            // Asegurarse de que el partido esté seleccionado
            if (partidoSeleccionadoId !== partidoId) {
                // Si no está seleccionado, seleccionarlo primero
                const card = document.querySelector('[onclick*="seleccionarPartido(' + partidoId + ',"]');
                if (card) {
                    seleccionarPartido(partidoId, siglas, card);
                }
            }
            
            // Guardar el nombre del partido para el modal
            partidoConfirmarTexto = siglas;
            origenConfirmacion = 'tarjeta';
            
            // Mostrar modal de confirmación
            document.getElementById('partidoConfirmar').textContent = siglas;
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
            modal.show();
        }

        function procesarVotoConfirmado() {
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmacion'));
            modal.hide();
            
            // Marcar como enviado para desactivar beforeunload
            votoEnviado = true;
            
            // Deshabilitar botones
            document.getElementById('btnConfirmar').disabled = true;
            document.getElementById('btnConfirmar').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> PROCESANDO VOTO...';
            
            // Deshabilitar todos los botones de confirmar en las tarjetas
            document.querySelectorAll('.btn-confirmar-partido').forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>PROCESANDO...';
            });
            
            clearInterval(temporizadorInterval);
            
            // Enviar el formulario
            document.getElementById('formVotacion').submit();
        }

        // Validar antes de enviar (para el botón general de abajo)
        document.getElementById('formVotacion').addEventListener('submit', function(e) {
            // Allow BLANCO votes even when no partidoSeleccionadoId is set
            const tipoInput = document.getElementById('tipo_voto');
            const tipoActual = tipoInput ? tipoInput.value : 'VALIDO';

            if (tipoActual !== 'BLANCO' && !partidoSeleccionadoId) {
                e.preventDefault();
                alert('⚠️ Por favor selecciona un candidato antes de confirmar');
                return false;
            }

            // Si ya se envió con el modal, permitir el envío
            if (votoEnviado) {
                return true;
            }

            // Prevenir envío y mostrar modal
            e.preventDefault();

            // Obtener el nombre del partido seleccionado o tipo
            const mensajeSeleccion = document.getElementById('partidoSeleccionado').textContent || (tipoActual === 'BLANCO' ? 'VOTO EN BLANCO' : '');
            partidoConfirmarTexto = mensajeSeleccion;
            origenConfirmacion = 'general';

            // Mostrar modal de confirmación
            document.getElementById('partidoConfirmar').textContent = mensajeSeleccion;
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
            modal.show();

            return false;
        });

        // Prevenir salida accidental SOLO si NO se ha enviado el voto
        window.addEventListener('beforeunload', function(e) {
            // Solo mostrar advertencia si hay selección Y no se ha enviado
            if (partidoSeleccionadoId && !votoEnviado) {
                e.preventDefault();
                e.returnValue = '¿Estás seguro de salir? Tu voto no ha sido confirmado.';
                return e.returnValue;
            }
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
