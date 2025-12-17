# 🚀 Inicio Rápido - Moodle AI Feedback Plugin

## Instalación Express (5 minutos)

### Paso 1: Verificar requisitos previos ✅

Asegúrate de tener instalado:
- Docker Desktop (https://www.docker.com/products/docker-desktop)
- Docker debe estar corriendo

### Paso 2: Levantar el entorno 🐳

Abre PowerShell en este directorio y ejecuta:

```powershell
.\start.ps1
```

O alternativamente:

```powershell
docker-compose up -d --build
```

⏱️ **Espera**: La primera vez tomará 5-10 minutos (descarga e instalación de Moodle)

### Paso 3: Monitorear la instalación 👀

En otra terminal PowerShell:

```powershell
docker logs -f moodle-app
```

Verás mensajes como:
- "Esperando a que MariaDB esté lista..."
- "Instalando Moodle por primera vez..."
- "Plugin AI Feedback instalado!"

### Paso 4: Acceder a Moodle 🎓

Cuando veas "Plugin AI Feedback instalado!", abre tu navegador:

**URL**: http://localhost:8080

**Credenciales**:
- Usuario: `admin`
- Contraseña: `Admin123!`

---

## Configuración Inicial del Plugin ⚙️

### 1. Configurar API Key Global

1. Inicia sesión como admin
2. Ve a: **Administración del sitio** (⚙️ icono arriba a la derecha)
3. Busca: **Plugins** → **Feedback de tareas** → **AI Feedback**
4. Ingresa tu **OpenAI API Key** (empieza con `sk-...`)
5. Selecciona modelo: **gpt-3.5-turbo** (recomendado para empezar)
6. Guardar cambios

### 2. Crear un Curso de Prueba

1. En el Dashboard, click **Mis cursos** → **Crear nuevo curso**
2. Nombre: "Curso de Prueba IA"
3. Guarda y entra al curso

### 3. Crear una Tarea con IA

1. Activa **Edición** (botón arriba)
2. **Agregar una actividad o recurso** → **Tarea**
3. Configuración básica:
   - **Nombre**: "Ensayo con evaluación IA"
   - **Descripción**: "Escribe un ensayo de 500 palabras sobre IA en educación"
   
4. En **Submission types** (Tipos de entrega):
   - ☑️ Activar "File submissions" (Entregas de archivo)
   - Tipos aceptados: `.txt,.pdf,.docx`

5. En **Feedback types** (Tipos de feedback):
   - ☑️ Activar "Feedback comments" (Comentarios de retroalimentación)
   - ☑️ Activar "AI Feedback"

6. Expandir **AI Feedback**:
   - ☑️ **Activar feedback automático con IA**: SÍ
   - **Rúbrica**: Click "Agregar..." y sube un PDF con la rúbrica
     - (Puedes usar: `ejemplos/rubrica_ejemplo.md` convertido a PDF)
   - **Modelo**: gpt-3.5-turbo

7. **Guardar y mostrar**

---

## Prueba Rápida 🧪

### Como Estudiante:

1. Abre una ventana de incógnito / navegación privada
2. Ve a: http://localhost:8080
3. Haz click en **Curso de Prueba IA** (si es público) o:
   - Crear un usuario estudiante desde admin
   - Inscribir al usuario en el curso

4. Entra a la tarea "Ensayo con evaluación IA"
5. Sube el archivo de ejemplo: `ejemplos/entrega_ejemplo.txt`
6. Click **Guardar cambios**
7. Espera 15-30 segundos
8. Actualiza la página
9. ¡Verás el feedback generado por IA! ✨

---

## Comandos Útiles 🛠️

### Iniciar Moodle
```powershell
.\start.ps1
```

### Detener Moodle
```powershell
.\stop.ps1
```

### Ver logs en tiempo real
```powershell
docker logs -f moodle-app
```

### Reiniciar contenedores
```powershell
docker-compose restart
```

### Ejecutar comandos dentro del contenedor
```powershell
docker exec -it moodle-app bash
```

### Limpiar todo y empezar de cero
```powershell
docker-compose down -v
docker-compose up -d --build
```

---

## Verificar que el Plugin está Instalado ✓

1. Admin → **Administración del sitio**
2. **Plugins** → **Resumen de plugins**
3. Buscar: "AI Feedback"
4. Debe aparecer en la lista como "assignfeedback_ai"

---

## Solución de Problemas Comunes 🔧

### "No puedo acceder a localhost:8080"

```powershell
# Verificar que los contenedores están corriendo
docker ps

# Deberías ver:
# - moodle-app
# - moodle-mariadb
```

### "El plugin no aparece instalado"

```powershell
# Ejecutar manualmente:
docker exec moodle-app php admin/cli/upgrade.php --non-interactive
docker exec moodle-app php admin/cli/purge_caches.php
```

### "Error al procesar la entrega"

1. Verificar que la rúbrica está cargada (PDF)
2. Verificar que la API Key es correcta
3. Ver logs: `docker logs moodle-app`

### "Composer/vendor no encontrado"

```powershell
docker exec -it moodle-app bash
cd /var/www/html/mod/assign/feedback/ai
composer install --no-dev
```

---

## Estructura de Archivos Creados 📁

```
MOODLE_NUEVO/
├── docker-compose.yml      # Configuración de Docker
├── Dockerfile             # Imagen de Moodle
├── docker-entrypoint.sh   # Script de inicialización
├── start.ps1              # Iniciar fácilmente
├── stop.ps1               # Detener fácilmente
├── README.md              # Documentación completa
├── GUIA_USO.md           # Guía detallada de uso
├── INICIO_RAPIDO.md      # Este archivo
├── ejemplos/
│   ├── rubrica_ejemplo.md
│   └── entrega_ejemplo.txt
└── plugin/                # Código del plugin
    ├── version.php
    ├── lib.php
    ├── settings.php
    ├── composer.json
    ├── classes/
    │   ├── ai_service.php
    │   ├── content_extractor.php
    │   ├── observer.php
    │   └── privacy/
    ├── db/
    └── lang/
```

---

## Siguiente Paso 🎯

Lee la [**Guía de Uso Completa**](GUIA_USO.md) para aprender:
- Cómo crear rúbricas efectivas
- Mejores prácticas de evaluación con IA
- Casos de uso avanzados
- Interpretación del feedback

---

## Soporte 💬

¿Problemas? Revisa:
1. ✅ [README.md](README.md) - Documentación técnica completa
2. ✅ [GUIA_USO.md](GUIA_USO.md) - Guía de uso para profesores y estudiantes
3. ✅ Logs de Docker: `docker logs moodle-app`

---

**¡Listo para evaluar con IA!** 🚀🤖📚
