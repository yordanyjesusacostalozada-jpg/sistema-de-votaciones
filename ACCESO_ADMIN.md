# 🔐 Acceso al Panel Administrativo

## Credenciales de Administrador

Para acceder al dashboard de resultados y funciones administrativas:

### URL de Acceso
```
http://localhost/elecciones_peru_2026/admin/login_admin.php
```

### Credenciales de Prueba

**Super Administrador:**
- Usuario: `admin`
- Contraseña: `admin123`
- Rol: SUPERADMIN

**Observador Electoral:**
- Usuario: `observador`
- Contraseña: `observador123`
- Rol: OBSERVADOR

---

## 🎯 Funcionalidades Implementadas

### Seguridad Electoral
✅ **Resultados protegidos**: Solo administradores pueden ver estadísticas
✅ **Autenticación requerida**: Login obligatorio para acceder al dashboard
✅ **Sesiones separadas**: Ciudadanos y administradores tienen sesiones independientes
✅ **Sin acceso público**: Los votantes ya no pueden ver resultados en tiempo real

### Flujo de Votante
1. Login con DNI
2. Votación en cédula digital
3. Confirmación de voto
4. **Ya NO pueden ver resultados** (seguridad mejorada)

### Flujo de Administrador
1. Login en `/admin/login_admin.php`
2. Dashboard administrativo
3. Acceso a resultados en tiempo real
4. Gestión del sistema electoral

---

## 📊 Dashboard de Administración

El panel incluye:
- ✅ **Resultados en Tiempo Real**: Estadísticas y gráficos completos
- ✅ **Gestión de Administradores**: Crear y administrar usuarios (solo SUPERADMIN)
- 🔜 **Gestión de Votos**: Administrar votos registrados
- 🔜 **Padrón Electoral**: Gestionar ciudadanos habilitados
- 🔜 **Partidos Políticos**: Administrar partidos y candidatos
- 🔜 **Reportes**: Exportar datos electorales

---

## 👥 Gestión de Administradores

### Crear Nuevos Administradores desde la Web

**URL:**
```
http://localhost/elecciones_peru_2026/admin/gestionar_administradores.php
```

**Requisitos:**
- ⚠️ Solo accesible para usuarios con rol **SUPERADMIN**
- Login requerido

**Funcionalidades:**
- ✅ **Crear nuevos administradores** con formulario web
- ✅ **Asignar roles**: SUPERADMIN, MODERADOR, OBSERVADOR
- ✅ **Activar/Desactivar** cuentas de administradores
- ✅ **Cambiar contraseñas** de otros administradores
- ✅ **Ver último acceso** de cada administrador
- ✅ **Validación**: No permite crear usuarios duplicados

### Roles Disponibles

1. **SUPERADMIN**: Acceso total + gestión de administradores
2. **MODERADOR**: Acceso a resultados y gestión general
3. **OBSERVADOR**: Solo lectura de resultados

---

## 🛡️ Seguridad Implementada

- Validación de sesión en cada página protegida
- Redirección automática si no está autenticado
- Información del admin visible en el header
- Botón de cerrar sesión en todas las páginas protegidas
- Contraseñas encriptadas con MD5 (recomendación: migrar a bcrypt)

---

## 📝 Notas Importantes

⚠️ **IMPORTANTE**: En producción, cambiar las credenciales por defecto y usar un algoritmo de hash más seguro (bcrypt en lugar de MD5).

🔒 **Acceso Restringido**: El archivo `resultados_publicos.php` ahora requiere autenticación administrativa.
