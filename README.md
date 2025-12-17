# Plugin de Feedback Automático con IA para Moodle

Plugin para Moodle que genera automáticamente feedback y calificaciones para entregas de estudiantes utilizando la API de OpenAI, basándose en una rúbrica de evaluación predefinida por el profesor.

## 🚀 Características

- ✅ Evaluación automática con IA (OpenAI GPT-3.5/GPT-4)
- 📄 Soporte para archivos TXT, PDF y DOCX
- 📋 Evaluación basada en rúbricas personalizadas
- 🔒 Cumplimiento con GDPR
- 🌐 Multiidioma (Español e Inglés)
- 🐳 Entorno Docker completo incluido

## 📋 Requisitos

- Moodle 4.0 o superior
- PHP 8.0 o superior
- MariaDB 10.11 o MySQL 5.7+
- Docker y Docker Compose (para desarrollo)
- API Key de OpenAI

## 🛠️ Instalación con Docker (Desarrollo)

### Paso 1: Clonar o descargar el proyecto

```bash
cd c:\Users\victo\Desktop\TESIS_MICHELLE\MOODLE_NUEVO
```

### Paso 2: Levantar los contenedores

```powershell
docker-compose up -d
```

Este comando:
- Descarga e instala Moodle 4.0.4
- Configura MariaDB 10.11
- Instala el plugin automáticamente
- Instala las dependencias de PHP vía Composer

### Paso 3: Esperar a que termine la instalación

Monitorear los logs (esto puede tomar 3-5 minutos):

```powershell
docker logs -f moodle-app
```

Espera a ver el mensaje: "Moodle instalado correctamente!"

### Paso 4: Acceder a Moodle

Abre tu navegador en: **http://localhost:8080**

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `Admin123!`

### Paso 5: Verificar instalación del plugin

1. Ve a **Administración del sitio** → **Plugins** → **Resumen de plugins**
2. Busca "AI Feedback" en la lista
3. Debe aparecer como instalado y activo

## ⚙️ Configuración

### Configuración Global del Plugin

1. Ve a **Administración del sitio** → **Plugins** → **Feedback de tareas** → **AI Feedback**
2. Configura:
   - **API Key global de OpenAI**: Tu clave de API (opcional)
   - **Modelo por defecto**: `gpt-3.5-turbo` (recomendado) o `gpt-4`

### Configuración por Tarea

1. Crea o edita una **Tarea** (Assignment)
2. En la configuración de **Tipos de feedback**, activa:
   - ☑️ **Comentarios de retroalimentación**
   - ☑️ **AI Feedback**
3. En la sección **AI Feedback**:
   - ☑️ Activar feedback automático con IA
   - 📄 Subir rúbrica de evaluación (PDF, máx 5MB)
   - 🔑 API Key de OpenAI (opcional si hay una global)
   - 🤖 Modelo: Selecciona `gpt-3.5-turbo` o `gpt-4`
4. Guarda los cambios

## 📖 Uso

### Para Profesores

1. **Crear tarea con IA activada** (según configuración anterior)
2. Los estudiantes suben sus archivos (TXT, PDF o DOCX)
3. El plugin procesa automáticamente la entrega
4. El feedback y calificación aparecen en la tarea del estudiante
5. El profesor puede **revisar y modificar** manualmente si lo desea

### Para Estudiantes

1. Acceder a la tarea asignada
2. Subir archivo (TXT, PDF o DOCX)
3. Esperar 10-30 segundos
4. El feedback aparece automáticamente en la entrega

## 🔧 Comandos Útiles de Docker

### Ver logs en tiempo real
```powershell
docker logs -f moodle-app
```

### Reiniciar contenedores
```powershell
docker-compose restart
```

### Detener contenedores
```powershell
docker-compose down
```

### Eliminar todo (incluyendo datos)
```powershell
docker-compose down -v
```

### Reinstalar plugin
```powershell
# Entrar al contenedor
docker exec -it moodle-app bash

# Reinstalar dependencias
cd /var/www/html/mod/assign/feedback/ai
composer install --no-dev

# Ejecutar upgrade de Moodle
cd /var/www/html
php admin/cli/upgrade.php --non-interactive
php admin/cli/purge_caches.php
```

### Ver estado de la base de datos
```powershell
docker exec -it moodle-mariadb mysql -u moodleuser -pmoodlepass -D moodle
```

Consultas útiles:
```sql
-- Ver configuraciones del plugin
SELECT * FROM mdl_assignfeedback_ai_config;

-- Ver calificaciones generadas
SELECT * FROM mdl_assign_grades WHERE grader = -1;

-- Ver feedback de IA
SELECT * FROM mdl_assignfeedback_comments;
```

## 📁 Estructura del Plugin

```
plugin/
├── version.php                 # Metadatos del plugin
├── lib.php                     # Funciones principales
├── settings.php                # Configuración global
├── composer.json               # Dependencias PHP
├── lang/                       # Traducciones
│   ├── es/
│   │   └── assignfeedback_ai.php
│   └── en/
│       └── assignfeedback_ai.php
├── classes/
│   ├── ai_service.php          # Integración OpenAI
│   ├── content_extractor.php   # Extractor de archivos
│   ├── observer.php            # Eventos de Moodle
│   └── privacy/
│       └── provider.php        # GDPR compliance
└── db/
    ├── install.xml             # Esquema de BD
    ├── upgrade.php             # Actualizaciones
    ├── access.php              # Permisos
    └── events.php              # Observers
```

## 🔒 Seguridad y Privacidad

- Las API Keys se almacenan de forma segura en la base de datos
- Los estudiantes **no** ven las configuraciones del plugin
- Solo profesores y administradores pueden configurar el plugin
- Cumple con GDPR (implementado Privacy API de Moodle)
- Las entregas se envían a OpenAI para procesamiento (informar a estudiantes)

## 🐛 Troubleshooting

### Plugin no aparece instalado

```powershell
docker exec moodle-app php admin/cli/upgrade.php --non-interactive
docker exec moodle-app php admin/cli/purge_caches.php
```

### Error al extraer contenido de archivos

Verificar instalación de dependencias:
```powershell
docker exec -it moodle-app bash
cd /var/www/html/mod/assign/feedback/ai
composer install --no-dev
```

### Error de permisos

```powershell
docker exec moodle-app chown -R www-data:www-data /var/www/html/mod/assign/feedback/ai
docker exec moodle-app chmod -R 755 /var/www/html/mod/assign/feedback/ai
```

### Error de conexión a OpenAI

Verificar API Key:
```powershell
docker exec -it moodle-app bash
curl https://api.openai.com/v1/models -H "Authorization: Bearer TU_API_KEY"
```

### Ver logs de debug de Moodle

1. Ve a **Administración del sitio** → **Desarrollo** → **Depuración**
2. Activa **DEVELOPER: mensajes de depuración extra**
3. Revisa los logs en `/var/www/moodledata`

## 🧪 Testing

### Caso de prueba 1: Entrega TXT
1. Crear tarea con IA activada
2. Subir rúbrica PDF
3. Estudiante sube archivo .txt
4. Verificar feedback automático

### Caso de prueba 2: Entrega PDF
1. Estudiante sube archivo .pdf
2. Verificar extracción de contenido
3. Verificar feedback generado

### Caso de prueba 3: Entrega DOCX
1. Estudiante sube archivo .docx
2. Verificar feedback y calificación

### Caso de prueba 4: Modificación manual
1. Profesor modifica calificación generada por IA
2. Profesor agrega comentarios adicionales
3. Verificar que los cambios se guardan

## 📚 Recursos

- [Documentación de Moodle](https://docs.moodle.org)
- [API de OpenAI](https://platform.openai.com/docs)
- [Desarrollo de Plugins para Moodle](https://moodledev.io)

## 📄 Licencia

GPL v3 o posterior (compatible con Moodle)

## 👥 Soporte

Para problemas o preguntas:
1. Revisar la sección de Troubleshooting
2. Verificar logs de Docker
3. Consultar documentación de Moodle

## 🎓 Créditos

Desarrollado como proyecto de tesis para implementación de IA en plataformas educativas.

---

**Última actualización**: Diciembre 2025  
**Versión del plugin**: 1.0  
**Compatible con**: Moodle 4.0+
