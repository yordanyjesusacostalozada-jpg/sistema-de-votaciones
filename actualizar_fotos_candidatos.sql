-- Actualizar fotos de candidatos con las rutas correctas

-- Fuerza Popular (FP)
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/FP-KEIKO FUJIMORI HIGUCHI.jpeg' WHERE partido_id = 1 AND tipo_candidato = 'PRESIDENTE';
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/FP-LUIS GALARRETA VELARDE.jpeg' WHERE partido_id = 1 AND tipo_candidato = 'VICEPRESIDENTE_1';
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/FP-MARTHA CHAVEZ COSSIO.jpeg' WHERE partido_id = 1 AND tipo_candidato = 'VICEPRESIDENTE_2';

-- Perú Libre (PL)
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/PL-PEDRO CASTILLO TERRONES.jpeg' WHERE partido_id = 2 AND tipo_candidato = 'PRESIDENTE';
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/PL-DINA BOLUARTE ZEGARRA.jpeg' WHERE partido_id = 2 AND tipo_candidato = 'VICEPRESIDENTE_1';

-- Renovación Popular (RP)
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/RP-RAFAEL LOPEZ ALIAGA.jpeg' WHERE partido_id = 3 AND tipo_candidato = 'PRESIDENTE';
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/RP-ADRIANA TUDELA GUTIÉRREZ.jpeg' WHERE partido_id = 3 AND tipo_candidato = 'VICEPRESIDENTE_1';

-- Alianza para el Progreso (APP)
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/APP-CESAR ACUÑA PERALTA.jpeg' WHERE partido_id = 4 AND tipo_candidato = 'PRESIDENTE';
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/APP-LADY CAMONES SORIANO.jpeg' WHERE partido_id = 4 AND tipo_candidato = 'VICEPRESIDENTE_1';

-- Acción Popular (AP)
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/AP-YONHY LESCANO ANCIETA.jpeg' WHERE partido_id = 5 AND tipo_candidato = 'PRESIDENTE';
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/AP-MARIA ISABEL LEON.jpeg' WHERE partido_id = 5 AND tipo_candidato = 'VICEPRESIDENTE_1';

-- Partido Morado (PM)
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/PM-JULIO GUZMAN CACERES.jpeg' WHERE partido_id = 6 AND tipo_candidato = 'PRESIDENTE';
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/PM-FLOR PABLO MEDINA.jpeg' WHERE partido_id = 6 AND tipo_candidato = 'VICEPRESIDENTE_1';

-- Avanza País (APPIS)
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/APPPIS-HERNANDO DE SOTO POLAR.jpeg' WHERE partido_id = 7 AND tipo_candidato = 'PRESIDENTE';
UPDATE tbl_candidato SET foto_url = 'assets/img/candidatos/APPIS-PATRICIA CHIRINOS VENEGAS.jpeg' WHERE partido_id = 7 AND tipo_candidato = 'VICEPRESIDENTE_1';
