# Script para iniciar Moodle con el plugin AI Feedback
# Ejecutar en PowerShell: .\start.ps1

Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  Iniciando Moodle AI Feedback" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Verificar si Docker está corriendo
$dockerRunning = docker info 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Docker no está corriendo" -ForegroundColor Red
    Write-Host "Por favor, inicia Docker Desktop primero" -ForegroundColor Yellow
    exit 1
}

Write-Host "Iniciando contenedores..." -ForegroundColor Yellow
docker-compose up -d

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Contenedores iniciados exitosamente!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Accede a Moodle en: http://localhost:8080" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Credenciales:" -ForegroundColor Yellow
    Write-Host "  Usuario: admin" -ForegroundColor White
    Write-Host "  Contraseña: Admin123!" -ForegroundColor White
    Write-Host ""
    Write-Host "Ver logs: docker logs -f moodle-app" -ForegroundColor Gray
    Write-Host "Detener: docker-compose down" -ForegroundColor Gray
    Write-Host ""
} else {
    Write-Host "ERROR: No se pudieron iniciar los contenedores" -ForegroundColor Red
    Write-Host "Ejecuta: docker-compose logs" -ForegroundColor Yellow
}
