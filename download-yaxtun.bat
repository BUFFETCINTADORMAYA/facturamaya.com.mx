@echo off
chcp 65001 >nul
color 0B
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║           🌟 YAXTUN-OmniCore - Descargador Automático             ║
echo ║     Sistema Profesional de Reparación de Dispositivos Móviles        ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

REM Verificar PowerShell
echo [*] Verificando herramientas...
powershell -Command "Write-Host '    ✅ PowerShell disponible'" 2>nul || (
    echo     ❌ PowerShell no encontrado
    pause
    exit /b 1
)

REM Crear directorio de destino
set "DEST_DIR=%USERPROFILE%\Yaxtun-OmniCore"

echo.
echo [*] Descargando Yaxtun-OmniCore desde GitHub...
echo     Destino: %DEST_DIR%
echo.

REM Descargar ZIP desde GitHub
powershell -Command "$ProgressPreference = 'SilentlyContinue'; Invoke-WebRequest -Uri 'https://github.com/BUFFETCINTADORMAYA/facturamaya.com.mx/archive/refs/heads/repair-system-dev.zip' -OutFile '%TEMP%\yaxtun-download.zip'"

if %errorlevel% neq 0 (
    echo ❌ Error descargando archivo
    echo Verifica tu conexión a internet
    pause
    exit /b 1
)

echo ✅ Descarga completada
echo.
echo [*] Extrayendo archivos...

REM Extraer ZIP
powershell -Command "Expand-Archive -Path '%TEMP%\yaxtun-download.zip' -DestinationPath '%TEMP%\yaxtun-extract' -Force"

if %errorlevel% neq 0 (
    echo ❌ Error extrayendo archivos
    pause
    exit /b 1
)

echo ✅ Archivos extraídos
echo.
echo [*] Copiando Yaxtun-OmniCore...

REM Copiar carpeta Yaxtun-OmniCore
if exist "%DEST_DIR%" rmdir /s /q "%DEST_DIR%"
mkdir "%DEST_DIR%"

REM Buscar y copiar la carpeta correcta
for /d %%A in ("%TEMP%\yaxtun-extract\*") do (
    if exist "%%A\Yaxtun-OmniCore" (
        xcopy "%%A\Yaxtun-OmniCore" "%DEST_DIR%" /E /I /Y >nul
    )
)

if not exist "%DEST_DIR%\START.bat" (
    echo ❌ Error: No se encontró Yaxtun-OmniCore
    pause
    exit /b 1
)

echo ✅ Copia completada
echo.
echo [*] Limpiando archivos temporales...

REM Limpiar temporales
rmdir /s /q "%TEMP%\yaxtun-extract" 2>nul
del /f /q "%TEMP%\yaxtun-download.zip" 2>nul

echo ✅ Limpieza completada
echo.
echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║              🎉 ¡YAXTUN-OmniCore DESCARGADO EXITOSAMENTE!              ║
echo ╠══════════════════════════════════════════════════════════════════════════════╣
echo ║  📂 Ubicación: %DEST_DIR%                                  ║
echo ║                                                                  ║
echo ║  🕐 Próximos pasos:                                             ║
echo ║     1. Abre la carpeta: %DEST_DIR%                ║
echo ║     2. Doble click en: START.bat                                 ║
echo ║     3. Espera 30-60 segundos                                     ║
echo ║     4. El navegador abre http://localhost:3000 automáticamente  ║
echo ║                                                                  ║
echo ║  ✨ ¡El sistema está listo para usar!                             ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo Presiona cualquier tecla para abrir la carpeta...
pause >nul

REM Abrir carpeta en Explorer
start "" "%DEST_DIR%"

echo.
echo Para ejecutar: Doble click en START.bat
echo.
pause
