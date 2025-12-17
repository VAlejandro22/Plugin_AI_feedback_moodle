# Script de instalación rápida para Windows PowerShell

Write-Host "=================================" -ForegroundColor Cyan
Write-Host "Instalador de Moodle AI Feedback" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan
Write-Host ""

# Verificar Docker
Write-Host "Verificando Docker..." -ForegroundColor Yellow
$dockerInstalled = Get-Command docker -ErrorAction SilentlyContinue
if (-not $dockerInstalled) {
    Write-Host "ERROR: Docker no está instalado o no está en el PATH" -ForegroundColor Red
    Write-Host "Instala Docker Desktop desde: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}

Write-Host "✓ Docker encontrado" -ForegroundColor Green

# Verificar Docker Compose
$dockerComposeInstalled = Get-Command docker-compose -ErrorAction SilentlyContinue
if (-not $dockerComposeInstalled) {
    Write-Host "ERROR: Docker Compose no está instalado" -ForegroundColor Red
    exit 1
}

Write-Host "✓ Docker Compose encontrado" -ForegroundColor Green
Write-Host ""

# Preguntar por API Key
Write-Host "Configuración Opcional" -ForegroundColor Cyan
Write-Host "----------------------" -ForegroundColor Cyan
$apiKey = Read-Host "Ingresa tu API Key de OpenAI (opcional, presiona Enter para omitir)"

# Levantar contenedores
Write-Host ""
Write-Host "Levantando contenedores Docker..." -ForegroundColor Yellow
Write-Host "Esto puede tomar varios minutos la primera vez..." -ForegroundColor Yellow
Write-Host ""

docker-compose up -d

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Fallo al levantar los contenedores" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "✓ Contenedores iniciados" -ForegroundColor Green
Write-Host ""

# Esperar a que MariaDB esté lista
Write-Host "Esperando a que MariaDB esté lista..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Esperar a que Moodle termine de instalarse
Write-Host "Esperando a que Moodle termine de instalarse..." -ForegroundColor Yellow
Write-Host "Esto puede tomar 3-5 minutos. Monitoreando logs..." -ForegroundColor Yellow
Write-Host ""

$installed = $false
$maxAttempts = 60
$attempt = 0

while (-not $installed -and $attempt -lt $maxAttempts) {
    $attempt++
    $logs = docker logs moodle-app 2>&1
    
    if ($logs -match "Moodle instalado correctamente") {
        $installed = $true
        break
    }
    
    if ($logs -match "ERROR|FAILED") {
        Write-Host "Se detectaron errores en la instalación. Revisa los logs:" -ForegroundColor Red
        Write-Host "docker logs moodle-app" -ForegroundColor Yellow
        exit 1
    }
    
    Write-Host "." -NoNewline
    Start-Sleep -Seconds 5
}

Write-Host ""

if (-not $installed) {
    Write-Host "La instalación está tomando más tiempo del esperado." -ForegroundColor Yellow
    Write-Host "Puedes monitorear el progreso con:" -ForegroundColor Yellow
    Write-Host "docker logs -f moodle-app" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Presiona Enter cuando veas 'Moodle instalado correctamente!'" -ForegroundColor Yellow
    Read-Host
}

Write-Host ""
Write-Host "=================================" -ForegroundColor Green
Write-Host "¡Instalación Completada!" -ForegroundColor Green
Write-Host "=================================" -ForegroundColor Green
Write-Host ""
Write-Host "Accede a Moodle en: http://localhost:8080" -ForegroundColor Cyan
Write-Host ""
Write-Host "Credenciales de acceso:" -ForegroundColor Yellow
Write-Host "  Usuario: admin" -ForegroundColor White
Write-Host "  Contraseña: Admin123!" -ForegroundColor White
Write-Host ""
Write-Host "Próximos pasos:" -ForegroundColor Cyan
Write-Host "1. Inicia sesión en Moodle" -ForegroundColor White
Write-Host "2. Ve a Administración del sitio → Plugins → Resumen de plugins" -ForegroundColor White
Write-Host "3. Busca 'AI Feedback' para verificar la instalación" -ForegroundColor White
Write-Host "4. Configura tu API Key de OpenAI en:" -ForegroundColor White
Write-Host "   Administración del sitio → Plugins → Feedback de tareas → AI Feedback" -ForegroundColor White
Write-Host ""
Write-Host "Comandos útiles:" -ForegroundColor Cyan
Write-Host "  Ver logs: docker logs -f moodle-app" -ForegroundColor White
Write-Host "  Detener: docker-compose down" -ForegroundColor White
Write-Host "  Reiniciar: docker-compose restart" -ForegroundColor White
Write-Host ""
Write-Host "Para más información, consulta el README.md" -ForegroundColor Yellow
Write-Host ""
