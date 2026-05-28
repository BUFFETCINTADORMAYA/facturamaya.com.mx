#!/bin/bash

# PhoneRepairAI - Docker Entrypoint
# Script de inicialización del contenedor

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║         PhoneRepairAI - Sistema de Reparación             ║"
echo "║              Ambiente Containerizado - Docker              ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Función para verificar dependencias
check_dependencies() {
    echo -e "${YELLOW}[*] Verificando dependencias...${NC}"
    
    local missing=0
    
    # Verificar ADB
    if ! command -v adb &> /dev/null; then
        echo -e "${YELLOW}  ⚠️  ADB no encontrado, usando versión incluida${NC}"
        export PATH="/app/tools/platform-tools:$PATH"
    else
        echo -e "${GREEN}  ✅ ADB encontrado${NC}"
    fi
    
    # Verificar ideviceinfo para iOS
    if ! command -v ideviceinfo &> /dev/null; then
        echo -e "${YELLOW}  ⚠️  ideviceinfo no disponible - soporte iOS limitado${NC}"
    else
        echo -e "${GREEN}  ✅ iOS tools disponibles${NC}"
    fi
    
    # Verificar Python
    python_version=$(python3 --version 2>&1 | awk '{print $2}')
    echo -e "${GREEN}  ✅ Python $python_version${NC}"
    
    # Verificar Node
    if command -v node &> /dev/null; then
        node_version=$(node --version)
        echo -e "${GREEN}  ✅ Node $node_version${NC}"
    fi
}

# Función para inicializar directorios
init_directories() {
    echo -e "${YELLOW}[*] Inicializando directorios...${NC}"
    
    mkdir -p /app/backups/{android,ios,components}
    mkdir -p /app/logs/{android,ios}
    mkdir -p /app/database/{devices,roms,guides}
    mkdir -p /app/tools/cache
    
    echo -e "${GREEN}  ✅ Directorios creados${NC}"
}

# Función para inicializar base de datos
init_database() {
    echo -e "${YELLOW}[*] Inicializando base de datos...${NC}"
    
    if [ ! -f "/app/database/devices.json" ]; then
        python3 -c "
import json
devices = {
    'android': [],
    'ios': [],
    'repairs': []
}
with open('/app/database/devices.json', 'w') as f:
    json.dump(devices, f, indent=2)
        "
        echo -e "${GREEN}  ✅ Base de datos inicializada${NC}"
    else
        echo -e "${GREEN}  ✅ Base de datos existe${NC}"
    fi
}

# Función para configurar ADB server
setup_adb_server() {
    echo -e "${YELLOW}[*] Configurando ADB server...${NC}"
    
    # Iniciar ADB daemon
    adb start-server > /dev/null 2>&1 || true
    
    # Esperar a que esté listo
    sleep 2
    
    if adb devices &> /dev/null; then
        echo -e "${GREEN}  ✅ ADB server iniciado${NC}"
    else
        echo -e "${YELLOW}  ⚠️  ADB server status desconocido${NC}"
    fi
}

# Función para ejecutar sistema
run_system() {
    echo -e "${YELLOW}[*] Iniciando PhoneRepairAI...${NC}"
    
    # Determinar qué ejecutar
    case "${1:-full}" in
        api)
            echo -e "${BLUE}Iniciando API REST en puerto 5000...${NC}"
            python3 /app/api/server.py
            ;;
        web)
            echo -e "${BLUE}Iniciando interfaz web en puerto 3000...${NC}"
            cd /app/ui/dashboard && npm start
            ;;
        cli)
            echo -e "${BLUE}Iniciando CLI...${NC}"
            python3 /app/main.py
            ;;
        full|*)
            echo -e "${BLUE}Iniciando sistema completo...${NC}"
            
            # Iniciar API en background
            python3 /app/api/server.py &
            API_PID=$!
            echo -e "${GREEN}  ✅ API iniciada (PID: $API_PID)${NC}"
            
            # Esperar un poco
            sleep 2
            
            # Iniciar dashboard web
            cd /app/ui/dashboard && npm start &
            WEB_PID=$!
            echo -e "${GREEN}  ✅ Web iniciada (PID: $WEB_PID)${NC}"
            
            # Mostrar información de acceso
            echo -e "${GREEN}"
            echo "╔════════════════════════════════════════════════════════════╗"
            echo "║              🎉 PhoneRepairAI Iniciado                     ║"
            echo "╠════════════════════════════════════════════════════════════╣"
            echo "║  🌐 Web Dashboard:  http://localhost:3000                  ║"
            echo "║  📡 API REST:       http://localhost:5000                  ║"
            echo "║  🔌 ADB Server:     localhost:5037                         ║"
            echo "║                                                            ║"
            echo "║  📱 Escanear dispositivos: /api/devices                    ║"
            echo "║  🤖 Diagnosticar:         /api/diagnose                    ║"
            echo "║  🔧 Reparar:              /api/repair                     ║"
            echo "╚════════════════════════════════════════════════════════════╝"
            echo -e "${NC}"
            
            # Esperar indefinidamente
            wait
            ;;
    esac
}

# Función para test
run_test() {
    echo -e "${YELLOW}[*] Ejecutando tests...${NC}"
    
    python3 -m pytest /app/tests/ -v --tb=short 2>/dev/null || \
    python3 /app/test_system.py
}

# Main
main() {
    check_dependencies
    init_directories
    init_database
    setup_adb_server
    
    case "${1:-run}" in
        test)
            run_test
            ;;
        run|api|web|cli|full)
            run_system "$@"
            ;;
        bash|sh)
            /bin/bash
            ;;
        *)
            echo -e "${YELLOW}Uso: $0 {run|api|web|cli|full|test|bash}${NC}"
            echo ""
            echo "  run   - Ejecutar sistema completo (default)"
            echo "  api   - Solo API REST"
            echo "  web   - Solo dashboard web"
            echo "  cli   - Interface de línea de comandos"
            echo "  full  - Todos los servicios"
            echo "  test  - Ejecutar tests"
            echo "  bash  - Abrir shell interactivo"
            ;;
    esac
}

main "$@"
