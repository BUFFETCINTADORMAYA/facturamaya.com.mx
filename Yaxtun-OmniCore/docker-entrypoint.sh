#!/bin/bash

# YAXTUN-OmniCore - Docker Entrypoint
# Script principal de inicialización

set -e

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}"
echo "╔══════════════════════════════════════════════════════════════════════════════╗"
echo "║                  🌟 YAXTUN-OmniCore v2.0.0                         ║"
echo "║        Sistema Profesional de Reparación de Dispositivos Móviles        ║"
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${YELLOW}[*] Verificando dependencias...${NC}"

# ADB
if ! command -v adb &> /dev/null; then
    export PATH="/app/tools/platform-tools:$PATH"
    echo -e "${GREEN}  ✅ ADB (incluido) listo${NC}"
else
    echo -e "${GREEN}  ✅ ADB del sistema${NC}"
fi

# Python
python_version=$(python3 --version 2>&1 | awk '{print $2}')
echo -e "${GREEN}  ✅ Python $python_version${NC}"

echo -e "${YELLOW}[*] Inicializando directorios...${NC}"
mkdir -p /app/backups/{android,ios,components}
mkdir -p /app/logs/{android,ios}
mkdir -p /app/database/{devices,roms,guides,pdfs}
mkdir -p /app/tools/cache
echo -e "${GREEN}  ✅ Directorios listos${NC}"

echo -e "${YELLOW}[*] Inicializando base de datos...${NC}"
if [ ! -f "/app/database/config.json" ]; then
    python3 << 'EOF'
import json
config = {
    "app_name": "Yaxtun-OmniCore",
    "version": "2.0.0",
    "language": "es",
    "timezone": "America/Mexico_City",
    "currency": "MXN",
    "devices": [],
    "repairs": [],
    "templates": {
        "diagnostic": {"enabled": True, "include_signature": False},
        "repair": {"enabled": True, "include_signature": True}
    }
}
with open('/app/database/config.json', 'w') as f:
    json.dump(config, f, indent=2)
EOF
    echo -e "${GREEN}  ✅ Base de datos creada${NC}"
else
    echo -e "${GREEN}  ✅ Base de datos existente${NC}"
fi

echo -e "${YELLOW}[*] Configurando ADB server...${NC}"
adb start-server > /dev/null 2>&1 || true
sleep 2
echo -e "${GREEN}  ✅ ADB server listo${NC}"

echo -e "${YELLOW}[*] Iniciando servicios...${NC}"

case "${1:-run}" in
    run|full)
        echo -e "${GREEN}  ✅ Iniciando API REST en puerto 5000...${NC}"
        python3 /app/api/server.py &
        API_PID=$!
        
        sleep 3
        
        echo -e "${GREEN}  ✅ Iniciando Dashboard Web en puerto 3000...${NC}"
        cd /app/ui/dashboard && npm start &
        WEB_PID=$!
        
        sleep 3
        
        echo -e "${GREEN}"
        echo "╔══════════════════════════════════════════════════════════════════════════════╗"
        echo "║             🎉 ¡YAXTUN-OmniCore INICIADO CORRECTAMENTE!               ║"
        echo "╠══════════════════════════════════════════════════════════════════════════════╣"
        echo "║  🌐 PORTAL WEB:     http://localhost:3000                        ║"
        echo "║  📡 API REST:       http://localhost:5000                        ║"
        echo "║  🔌 ADB SERVER:     localhost:5037                               ║"
        echo "║                                                                  ║"
        echo "║  👤 Dashboard:      Abrir en navegador (ya debería estar abierto)  ║"
        echo "║  💲 Módulos:        Diagnóstico, Reparación, Órdenes, Reportes    ║"
        echo "╚══════════════════════════════════════════════════════════════════════════════╝"
        echo -e "${NC}"
        
        wait
        ;;
    bash|sh)
        /bin/bash
        ;;
    *)
        exec "$@"
        ;;
esac
