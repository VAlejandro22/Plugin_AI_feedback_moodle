# Script para detener Moodle
# Ejecutar en PowerShell: .\stop.ps1

Write-Host ""
Write-Host "Deteniendo contenedores de Moodle..." -ForegroundColor Yellow
docker-compose down

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Contenedores detenidos" -ForegroundColor Green
} else {
    Write-Host "ERROR al detener contenedores" -ForegroundColor Red
}
Write-Host ""
