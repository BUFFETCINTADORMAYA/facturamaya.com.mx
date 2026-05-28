# PhoneRepairAI - Script de Instalación para Windows (PowerShell)

$host.ui.RawUI.WindowTitle = "PhoneRepairAI Installer"

Write-Host "`n" -ForegroundColor Blue
Write-Host "╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Blue
Write-Host "║       PhoneRepairAI - Script de Instalación para Windows        ║" -ForegroundColor Blue
Write-Host "║    Sistema Completo Aislado en Contenedor Docker               ║" -ForegroundColor Blue
Write-Host "╚════════════════════════════════════════════════════════════════╝" -ForegroundColor Blue
Write-Host "`n"

# Verificar si Docker está instalado
Write-Host "[*] Verificando Docker..." -ForegroundColor Yellow
$docker = docker --version 2>$null
if ($docker) {
    Write-Host "✅ Docker encontrado: $docker" -ForegroundColor Green
} else {
    Write-Host "❌ Docker no está instalado" -ForegroundColor Red
    Write-Host "Descárgalo desde: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}

# Verificar Docker Compose
Write-Host "[*] Verificando Docker Compose..." -ForegroundColor Yellow
$compose = docker-compose --version 2>$null
if ($compose) {
    Write-Host "✅ Docker Compose encontrado: $compose" -ForegroundColor Green
} else {
    Write-Host "❌ Docker Compose no está instalado" -ForegroundColor Red
    exit 1
}

# Crear directorios
Write-Host "[*] Creando directorios..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "backups/android" | Out-Null
New-Item -ItemType Directory -Force -Path "backups/ios" | Out-Null
New-Item -ItemType Directory -Force -Path "logs/android" | Out-Null
New-Item -ItemType Directory -Force -Path "logs/ios" | Out-Null
New-Item -ItemType Directory -Force -Path "database/devices" | Out-Null
New-Item -ItemType Directory -Force -Path "database/roms" | Out-Null
Write-Host "✅ Directorios creados" -ForegroundColor Green

# Build imagen
Write-Host "[*] Construyendo imagen Docker..." -ForegroundColor Yellow
Write-Host "Esto puede tomar unos minutos..." -ForegroundColor Blue

if (docker-compose build) {
    Write-Host "✅ Imagen construida exitosamente" -ForegroundColor Green
} else {
    Write-Host "❌ Error construyendo imagen" -ForegroundColor Red
    exit 1
}

# Crear volúmenes
Write-Host "[*] Creando volúmenes Docker..." -ForegroundColor Yellow
docker volume create phonerepairal-backups 2>$null
docker volume create phonerepairal-logs 2>$null
docker volume create phonerepairal-database 2>$null
docker volume create phonerepairal-roms 2>$null
Write-Host "✅ Volúmenes creados" -ForegroundColor Green

# Iniciar contenedor
Write-Host "[*] Iniciando contenedor..." -ForegroundColor Yellow

if (docker-compose up -d) {
    Write-Host "✅ Contenedor iniciado" -ForegroundColor Green
} else {
    Write-Host "❌ Error iniciando contenedor" -ForegroundColor Red
    exit 1
}

# Esperar
Write-Host "[*] Esperando que el sistema inicie..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Verificar
$status = docker-compose ps | Select-String "Up"
if ($status) {
    Write-Host "✅ Sistema en ejecución" -ForegroundColor Green
} else {
    Write-Host "❌ Error: Sistema no está ejecutándose" -ForegroundColor Red
    docker-compose logs
    exit 1
}

# Mostrar información
Write-Host "`n" -ForegroundColor Green
Write-Host "╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║          🎉 PhoneRepairAI Instalado Exitosamente!              ║" -ForegroundColor Green
Write-Host "╠════════════════════════════════════════════════════════════════╣" -ForegroundColor Green
Write-Host "║  🌐 Web Dashboard:  http://localhost:3000                      ║" -ForegroundColor Green
Write-Host "║  📡 API REST:       http://localhost:5000                      ║" -ForegroundColor Green
Write-Host "║  🔌 ADB Server:     localhost:5037                             ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host "`n"

Write-Host "[*] Comandos útiles:" -ForegroundColor Yellow
Write-Host "  Ver estado:           docker-compose ps" -ForegroundColor Cyan
Write-Host "  Ver logs:             docker-compose logs -f" -ForegroundColor Cyan
Write-Host "  Acceder a shell:      docker-compose exec phonerepairal bash" -ForegroundColor Cyan
Write-Host "  Detener sistema:      docker-compose down" -ForegroundColor Cyan
Write-Host "  Reiniciar:            docker-compose restart" -ForegroundColor Cyan
Write-Host "`n"

Write-Host "[*] Para conectar un teléfono:" -ForegroundColor Yellow
Write-Host "  1. Conecta tu dispositivo vía USB" -ForegroundColor Cyan
Write-Host "  2. Abre el dashboard: http://localhost:3000" -ForegroundColor Cyan
Write-Host "  3. El sistema lo detectará automáticamente" -ForegroundColor Cyan
Write-Host "`n"

Write-Host "✅ ¡Instalación completada!" -ForegroundColor Green
Write-Host "`n"

Read-Host "Presiona Enter para abrir el dashboard"
Start-Process "http://localhost:3000"
