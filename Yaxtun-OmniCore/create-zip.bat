@echo off
REM Script para crear ZIP descargable de Yaxtun-OmniCore

echo Creando ZIP de Yaxtun-OmniCore...

REM Requiere 7-Zip o WinRAR instalado
REM O usar PowerShell

powershell -Command "Add-Type -AssemblyName 'System.IO.Compression.FileSystem'; [System.IO.Compression.ZipFile]::CreateFromDirectory('Yaxtun-OmniCore', 'Yaxtun-OmniCore-v2.0.0.zip')"

echo.
echo ZIP creado: Yaxtun-OmniCore-v2.0.0.zip
echo.
ls -lh Yaxtun-OmniCore-v2.0.0.zip
