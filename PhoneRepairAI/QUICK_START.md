# 🚀 Guía Rápida - PhoneRepairAI

## Instalación Rápida (Docker)

### Requisitos
- Docker Desktop instalado
- Docker Compose
- Puerto 3000, 5000, 5037 disponibles

### Paso 1: Descargar
```bash
git clone https://github.com/BUFFETCINTADORMAYA/facturamaya.com.mx.git
cd facturamaya.com.mx/PhoneRepairAI
```

### Paso 2: Instalar
```bash
# En Linux/Mac
bash install.sh

# En Windows (PowerShell)
powershell -ExecutionPolicy Bypass -File install.ps1
```

### Paso 3: Acceder
- **Dashboard:** http://localhost:3000
- **API:** http://localhost:5000
- **ADB Server:** localhost:5037

---

## Uso Rápido

### Diagnosticar Android
```bash
# Conectar teléfono Android
adb devices

# Desde el dashboard: click en "Detectar dispositivos"
# El sistema diagnosticará automáticamente
```

### Diagnosticar iPhone
```bash
# Conectar iPhone
# El dashboard mostrará el dispositivo
# Click en "Diagnóstico iOS"
```

### Flashear ROM (Android)
```bash
# 1. Dashboard → Dispositivos → Seleccionar
# 2. Click en "Flashear ROM"
# 3. Seleccionar ROM
# 4. Confirmar
```

### Desbloquear FRP
```bash
# 1. Dashboard → Herramientas → FRP Bypass
# 2. Seleccionar dispositivo
# 3. Confirmar desbloqueo
```

### Reparación iPhone (Cambio Pantalla)
```bash
# ANTES:
# 1. Dashboard → iOS → Extraer Componentes
# 2. Seleccionar "display" y "face_id"
# 3. Hacer backup

# DESPUÉS (nueva pantalla):
# 1. Dashboard → iOS → Restaurar Componentes
# 2. Seleccionar archivo de backup
# 3. Restaurar
# 4. Face ID funcionará ✅
```

---

## Comandos Docker

```bash
# Ver estado
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Acceder a terminal
docker-compose exec phonerepairal bash

# Detener
docker-compose down

# Reiniciar
docker-compose restart

# Reconstruir imagen
docker-compose build --no-cache
```

---

## API REST Endpoints

### Dispositivos
```bash
# Obtener dispositivos
GET http://localhost:5000/api/devices

# Información dispositivo
GET http://localhost:5000/api/devices/<udid>
```

### Diagnóstico
```bash
# Diagnosticar
POST http://localhost:5000/api/diagnose
Body: {"udid": "device_id"}

# Resultado
GET http://localhost:5000/api/diagnose/<id>
```

### Reparación
```bash
# Listar herramientas
GET http://localhost:5000/api/repair/tools

# Flashear ROM
POST http://localhost:5000/api/repair/flash
Body: {
  "udid": "device_id",
  "rom_path": "/path/to/rom.bin"
}
```

### iOS Específico
```bash
# Extraer componentes
POST http://localhost:5000/api/ios/extract-components
Body: {
  "udid": "device_id",
  "components": ["face_id", "battery", "display"]
}

# Restaurar componentes
POST http://localhost:5000/api/ios/restore-components
Body: {
  "udid": "device_id",
  "backup_file": "/path/to/backup.json"
}
```

---

## Solución de Problemas

### Docker no inicia
```bash
# Ver errores
docker-compose logs

# Reconstruir
docker-compose build --no-cache
```

### Dispositivos no se detectan
```bash
# Verificar permisos USB
lsusb  # Linux

# Reiniciar ADB
docker-compose exec phonerepairal adb kill-server
docker-compose exec phonerepairal adb start-server
```

### Puertos en uso
```bash
# Cambiar puertos en docker-compose.yml
ports:
  - "3001:3000"   # Cambiar 3000
  - "5001:5000"   # Cambiar 5000
```

---

## Características

✅ Diagnóstico de Android e iOS
✅ Flasheo de ROM
✅ Desbloqueo FRP
✅ Extracción de datos de iPhone
✅ Preservación de Face ID
✅ Sistema IA de diagnóstico
✅ Dashboard web interactivo
✅ API REST completa
✅ Completamente aislado en Docker
✅ Instalación rápida sin dependencias

---

## Soporte

📧 Email: support@phonerepairal.com
🐛 GitHub Issues: github.com/BUFFETCINTADORMAYA/facturamaya.com.mx
📚 Documentación: wiki.phonerepairal.com

---

**Versión:** 1.0.0  
**Última actualización:** Junio 2024
