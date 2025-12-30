# Plugin de Feedback Automático con IA para Moodle

Plugin para Moodle que genera automáticamente feedback y calificaciones para entregas de estudiantes utilizando la API de OpenAI, basándose en una rúbrica de evaluación predefinida por el profesor.

## 🚀 Características

- ✅ Evaluación automática con IA (OpenAI GPT-3.5-turbo, GPT-4o, GPT-4o-mini, GPT-4-turbo)
- 🤖 Detección de contenido generado por IA
- 📄 Soporte para archivos TXT, PDF y DOCX
- 📋 Evaluación basada en rúbricas personalizadas
- 🔒 Cumplimiento con GDPR
- 🌐 Multiidioma (Español e Inglés)
- 🐳 Entorno Docker completo incluido
- ⚡ Procesamiento automático en primera entrega

## 📋 Requisitos

- Docker y Docker Compose
- API Key de OpenAI
- (Opcional) Cuenta en Docker Hub para subir la imagen

---

## 🚀 Opción 1: Desplegar desde Docker Hub (RECOMENDADO)

### En cualquier máquina nueva:

```bash
# 1. Crear directorio
mkdir moodle-ia && cd moodle-ia

# 2. Descargar docker-compose de producción
curl -O https://raw.githubusercontent.com/tu-usuario/tu-repo/main/docker-compose.prod.yml

# 3. Configurar tu usuario de Docker Hub (editar docker-compose.prod.yml)
# Cambiar "tu-usuario" por tu usuario real de Docker Hub

# 4. Levantar
docker-compose -f docker-compose.prod.yml up -d

# 5. Acceder a http://localhost:8080 para completar la instalación
```

---

## 🛠️ Opción 2: Construcción Local (Desarrollo)

### Paso 1: Clonar el proyecto

```bash
git clone https://github.com/tu-usuario/MOODLE_NUEVO.git
cd MOODLE_NUEVO
```

### Paso 2: Levantar los contenedores

```powershell
docker-compose up -d
```

Este comando:
- Descarga e instala Moodle 4.4
- Configura MariaDB 10.11
- Instala el plugin automáticamente
- Inicia el cron de Moodle para procesar tareas

### Paso 3: Esperar a que termine la instalación

Monitorear los logs (esto puede tomar 3-5 minutos):

```powershell
docker logs -f moodle-app
```

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

---

## 🐳 Subir imagen a Docker Hub

### Paso 1: Crear cuenta en Docker Hub

1. Ve a https://hub.docker.com y crea una cuenta
2. Anota tu nombre de usuario (ej: `michelleuio`)

### Paso 2: Iniciar sesión en Docker

```powershell
docker login
# Ingresa tu usuario y contraseña de Docker Hub
```

### Paso 3: Construir la imagen

```powershell
cd c:\Users\victo\Desktop\TESIS_MICHELLE\MOODLE_NUEVO

# Construir imagen (reemplaza 'tu-usuario' con tu usuario de Docker Hub)
docker build -t tu-usuario/moodle-feedback-ia:latest .
```

### Paso 4: Subir a Docker Hub

```powershell
docker push tu-usuario/moodle-feedback-ia:latest
```

### Paso 5: Usar en otra máquina

En la nueva máquina, crea un archivo `docker-compose.yml`:

```yaml
version: '3.8'

services:
  moodle:
    image: tu-usuario/moodle-feedback-ia:latest
    container_name: moodle-app
    ports:
      - "8080:80"
    environment:
      - MOODLE_DATABASE_HOST=mariadb
      - MOODLE_DATABASE_NAME=moodle
      - MOODLE_DATABASE_USER=moodleuser
      - MOODLE_DATABASE_PASSWORD=moodlepass
    volumes:
      - moodledata:/var/www/moodledata
    depends_on:
      - mariadb
    restart: unless-stopped

  mariadb:
    image: mariadb:10.11
    container_name: moodle-mariadb
    environment:
      - MYSQL_ROOT_PASSWORD=rootpass
      - MYSQL_DATABASE=moodle
      - MYSQL_USER=moodleuser
      - MYSQL_PASSWORD=moodlepass
    volumes:
      - mariadb_data:/var/lib/mysql
    command: >
      --character-set-server=utf8mb4
      --collation-server=utf8mb4_unicode_ci
      --innodb_file_per_table=1
    restart: unless-stopped

volumes:
  moodledata:
  mariadb_data:
```

Luego ejecuta:

```powershell
docker-compose up -d
```

Y accede a `http://localhost:8080` para completar la instalación de Moodle.

---

## ⚙️ Instalación de Moodle (Primera vez)

Cuando accedas a `http://localhost:8080` por primera vez:

1. **Idioma**: Selecciona Español
2. **Rutas**: Mantén los valores por defecto
3. **Base de datos**:
   - Tipo: MariaDB
   - Host: `mariadb`
   - Nombre: `moodle`
   - Usuario: `moodleuser`
   - Contraseña: `moodlepass`
4. **Administrador**: Crea usuario admin (recuerda la contraseña)
5. **Nombre del sitio**: El que desees

Después de la instalación:
- Ve a **Administración del sitio** → **Plugins** → **Feedback de tareas** → **Feedback con IA**
- Configura tu API Key de OpenAI global (opcional)

---

## 📝 Licencia

GPL v3 - Compatible con la licencia de Moodle
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
