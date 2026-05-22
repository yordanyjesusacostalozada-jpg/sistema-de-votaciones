<?php
/**
 * SISTEMA ELECTORAL PERÚ 2026
 * Dashboard de Resultados en Tiempo Real
 * ACCESO RESTRINGIDO SOLO PARA ADMINISTRADORES
 */

session_start();

// Verificar que sea un administrador logueado
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin/login_admin.php");
    exit();
}

include 'conexion.php';

// Obtener estadísticas generales y resultados
if ($is_production) {
    // PostgreSQL
    $query_stats = "SELECT * FROM v_estadisticas_elecciones";
    $resultado_stats = pg_query($conexion, $query_stats);
    $stats_raw = pg_fetch_assoc($resultado_stats);
    
    // Normalizar estadísticas
    $stats = [
        'total_ciudadanos' => $stats_raw['total_ciudadanos'] ?? 0,
        'total_votantes' => $stats_raw['total_votantes'] ?? 0,
        'votos_validos' => $stats_raw['votos_validos'] ?? 0,
        'votos_blancos' => $stats_raw['votos_blancos'] ?? 0,
        'votos_nulos' => $stats_raw['votos_nulos'] ?? 0,
        'porcentaje_participacion' => $stats_raw['porcentaje_participacion'] ?? 0
    ];
    
    $query_resultados = "SELECT * FROM v_resultados_tiempo_real ORDER BY total_votos DESC";
    $resultado_partidos = pg_query($conexion, $query_resultados);
    $partidos = [];
    
    while ($fila = pg_fetch_assoc($resultado_partidos)) {
        // Normalizar nombres de campos para PostgreSQL
        $partidos[] = [
            'partido_id' => $fila['partido_id'],
            'siglas' => $fila['siglas'],
            'nombre_completo' => $fila['nombre_completo'],
            'nombre_corto' => $fila['siglas'], // Usar siglas como nombre corto
            'logo_url' => $fila['logo_url'],
            'color_primario' => $fila['color_primario'],
            'total_votos' => $fila['total_votos'] ?? 0,
            'votos' => $fila['total_votos'] ?? 0,
            'porcentaje' => $fila['porcentaje'] ?? 0,
            'candidato_presidente' => $fila['candidato_presidente'],
            'candidato_nombre' => $fila['candidato_presidente']
        ];
    }
    
    // Agregar total_partidos a stats
    $stats['total_partidos'] = count($partidos);
    
    pg_close($conexion);
} else {
    // MySQL
    $query_stats = "SELECT * FROM v_estadisticas_elecciones";
    $resultado_stats = mysqli_query($conexion, $query_stats);
    $stats = mysqli_fetch_assoc($resultado_stats);
    
    $query_resultados = "SELECT * FROM v_resultados_tiempo_real ORDER BY total_votos DESC";
    $resultado_partidos = mysqli_query($conexion, $query_resultados);
    $partidos = [];
    
    while ($fila = mysqli_fetch_assoc($resultado_partidos)) {
        $partidos[] = $fila;
    }
    
    // Agregar total_partidos a stats
    $stats['total_partidos'] = count($partidos);
    
    mysqli_close($conexion);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados en Tiempo Real - Elecciones 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            background: #0a0e27;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .header-dashboard {
            background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%);
            padding: 30px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .header-dashboard h1 {
            font-size: 36px;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .stats-container {
            margin: 30px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #1a1f3a 0%, #2d3561 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            border: 2px solid rgba(220, 20, 60, 0.3);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(220, 20, 60, 0.4);
        }

        .stat-icon {
            font-size: 40px;
            color: #DC143C;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 42px;
            font-weight: 700;
            color: white;
            margin: 0;
        }

        .stat-label {
            color: #a0a0a0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .candidato-card {
            background: linear-gradient(135deg, #1a1f3a 0%, #2d3561 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            border-left: 6px solid;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .candidato-card:hover {
            transform: translateX(5px);
        }

        .candidato-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .candidato-foto-resultado {
            width: 100px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }

        .candidato-nombre {
            font-size: 22px;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
        }

        .partido-siglas {
            font-size: 18px;
            color: #DC143C;
            font-weight: 700;
        }

        .votos-numero {
            font-size: 36px;
            font-weight: 700;
            color: #4CAF50;
        }

        .porcentaje-badge {
            background: #DC143C;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 24px;
            font-weight: 700;
            display: inline-block;
        }

        .progress-bar-custom {
            height: 30px;
            border-radius: 15px;
            background: rgba(255,255,255,0.1);
            overflow: hidden;
            margin-top: 15px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #DC143C 0%, #FF1744 100%);
            transition: width 1s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }

        .top-3-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: gold;
            color: #000;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.6);
            z-index: 10;
        }

        .top-3-badge.segundo {
            background: silver;
        }

        .top-3-badge.tercero {
            background: #CD7F32;
        }

        .chart-container {
            background: linear-gradient(135deg, #1a1f3a 0%, #2d3561 100%);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .actualizacion-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(76, 175, 80, 0.9);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 1000;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .btn-home {
            background: rgba(255,255,255,0.1);
            border: 2px solid white;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background: white;
            color: #DC143C;
        }

        @media (max-width: 768px) {
            .candidato-foto-resultado {
                width: 70px;
                height: 85px;
            }

            .candidato-nombre {
                font-size: 16px;
            }

            .votos-numero {
                font-size: 24px;
            }

            .actualizacion-badge {
                position: static;
                margin: 15px auto;
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Badge de Actualización -->
    <div id="actualizacion-badge" class="actualizacion-badge">
        <i class="fas fa-clock me-2"></i>
        Próxima actualización en 30 segundos
    </div>

    <!-- Header -->
    <div class="header-dashboard">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <i class="fas fa-chart-line fa-3x"></i>
                </div>
                <div class="col-md-6 text-center">
                    <h1>RESULTADOS EN TIEMPO REAL</h1>
                    <p class="mb-0" style="font-size: 18px;">Elecciones Generales Perú 2026 - ONPE</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex align-items-center justify-content-end gap-3">
                        <div class="text-end">
                            <small style="opacity: 0.8; display: block;">
                                <i class="fas fa-user-shield me-1"></i>Administrador
                            </small>
                            <strong><?php echo htmlspecialchars($_SESSION['admin_nombres']); ?></strong>
                        </div>
                        <a href="logout_admin.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Estadísticas Generales -->
        <div class="stats-container">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <i class="fas fa-users stat-icon"></i>
                        <h2 class="stat-value"><?php echo number_format($stats['total_ciudadanos']); ?></h2>
                        <p class="stat-label">Ciudadanos Habilitados</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <i class="fas fa-check-circle stat-icon"></i>
                        <h2 class="stat-value"><?php echo number_format($stats['total_votantes']); ?></h2>
                        <p class="stat-label">Votos Emitidos</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <i class="fas fa-chart-pie stat-icon"></i>
                        <h2 class="stat-value"><?php echo number_format($stats['porcentaje_participacion'], 2); ?>%</h2>
                        <p class="stat-label">Participación</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card text-center">
                        <i class="fas fa-flag stat-icon"></i>
                        <h2 class="stat-value"><?php echo number_format($stats['total_partidos']); ?></h2>
                        <p class="stat-label">Partidos en Contienda</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Barras -->
        <div class="chart-container">
            <h3 class="text-center mb-4"><i class="fas fa-chart-bar me-2"></i>Resultados por Candidato</h3>
            <canvas id="chartResultados" style="max-height: 400px;"></canvas>
        </div>

        <!-- Resultados Detallados por Candidato -->
        <h3 class="text-center mb-4 mt-5">
            <i class="fas fa-trophy me-2"></i>
            CONTEO DE VOTOS POR CANDIDATO
        </h3>

        <div id="resultadosContainer">
            <?php 
            $posicion = 1;
            foreach ($partidos as $partido): 
                $border_color = $partido['color_primario'];
            ?>
            <div class="candidato-card" style="border-left-color: <?php echo $border_color; ?>;">
                <?php if ($posicion <= 3): ?>
                    <div class="top-3-badge <?php echo $posicion == 2 ? 'segundo' : ($posicion == 3 ? 'tercero' : ''); ?>">
                        <?php echo $posicion; ?>°
                    </div>
                <?php endif; ?>

                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <?php if (!empty($partido['candidato_foto'])): ?>
                        <img src="<?php echo htmlspecialchars($partido['candidato_foto']); ?>" 
                             alt="<?php echo htmlspecialchars($partido['candidato_nombre']); ?>" 
                             class="candidato-foto-resultado"
                             onerror="this.onerror=null; this.src='assets/img/candidatos/placeholder.svg';">
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <div class="candidato-nombre">
                            <?php echo htmlspecialchars($partido['candidato_nombre']); ?>
                        </div>
                        <div class="partido-siglas">
                            <?php echo htmlspecialchars($partido['siglas']); ?> - 
                            <?php echo htmlspecialchars($partido['nombre_corto']); ?>
                        </div>
                    </div>

                    <div class="col-md-3 text-center">
                        <div class="votos-numero">
                            <?php echo number_format($partido['total_votos']); ?>
                        </div>
                        <small class="text-muted">VOTOS</small>
                    </div>

                    <div class="col-md-3 text-center">
                        <div class="porcentaje-badge">
                            <?php echo number_format($partido['porcentaje'], 2); ?>%
                        </div>
                    </div>
                </div>

                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: <?php echo $partido['porcentaje']; ?>%;">
                        <?php echo number_format($partido['porcentaje'], 2); ?>%
                    </div>
                </div>
            </div>
            <?php 
            $posicion++;
            endforeach; 
            ?>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5 mb-4">
            <p class="text-muted">
                <i class="fas fa-info-circle me-2"></i>
                Los resultados se actualizan automáticamente cada 30 segundos
            </p>
            <p class="text-muted">
                <small>Sistema Electoral ONPE - Simulación Elecciones 2026</small>
            </p>
        </div>
    </div>

    <script>
        // Datos para el gráfico
        const candidatos = <?php echo json_encode(array_column($partidos, 'candidato_nombre')); ?>;
        const votos = <?php echo json_encode(array_column($partidos, 'total_votos')); ?>;
        const colores = <?php echo json_encode(array_column($partidos, 'color_primario')); ?>;

        // Crear gráfico con Chart.js
        const ctx = document.getElementById('chartResultados').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: candidatos,
                datasets: [{
                    label: 'Votos',
                    data: votos,
                    backgroundColor: colores,
                    borderColor: colores,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        padding: 15,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Votos: ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'white',
                            font: {
                                size: 14
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: 'white',
                            font: {
                                size: 12
                            },
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Contador de actualización
        let segundosRestantes = 30;
        
        // Actualizar contador cada segundo
        const contadorInterval = setInterval(function() {
            segundosRestantes--;
            const badge = document.getElementById('actualizacion-badge');
            
            if (segundosRestantes > 0) {
                badge.innerHTML = `<i class="fas fa-clock me-2"></i>Próxima actualización en ${segundosRestantes} segundos`;
                badge.style.opacity = '1';
            } else {
                badge.innerHTML = '<i class="fas fa-sync-alt fa-spin me-2"></i>Actualizando datos...';
            }
        }, 1000);
        
        // Actualización automática cada 30 segundos (reducido el parpadeo)
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
