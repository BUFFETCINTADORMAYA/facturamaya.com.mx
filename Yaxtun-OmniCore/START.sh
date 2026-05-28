#!/bin/bash

# YAXTUN-OmniCore - Script de Inicio para Linux/Mac

set -e

# Colores
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

clear

echo -e "${BLUE}"
echo ""
echo "╔══════════════════════════════════════════════════════════════════════════════╗"
echo "║           🌟 YAXTUN-OmniCore - Sistema de Reparación de Dispositivos         ║"
echo "║                    Iniciando ambiente completamente aislado                   ║"
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo ""

# Verificar Docker
echo -e "${YELLOW}[*] Verificando Docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker no encontrado${NC}"
    echo ""
    echo -e "${YELLOW}Instala Docker desde: https://www.docker.com/products/docker-desktop${NC}"
    echo ""
    exit 1
fi

echo -e "${GREEN}✅ Docker disponible${NC}"

# Verificar docker-compose
echo -e "${YELLOW}[*] Verificando Docker Compose...${NC}"
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}❌ Docker Compose no encontrado${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Docker Compose disponible${NC}"

# Obtener directorio actual
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$CURRENT_DIR"

# Verificar archivos
if [ ! -f "docker-compose.yml" ]; then
    echo -e "${RED}❌ Error: docker-compose.yml no encontrado${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}[*] Construyendo contenedor Docker...${NC}"
echo -e "${BLUE}Esto puede tomar unos minutos la primera vez...${NC}"
echo ""

# Detener contenedores previos
docker-compose down 2>/dev/null || true

# Iniciar sistema
if docker-compose up -d; then
    echo -e "${GREEN}✅ Sistema iniciado correctamente${NC}"
else
    echo -e "${RED}❌ Error iniciando el sistema${NC}"
    echo ""
    echo "Intenta:"
    echo "  1. Asegúrate que Docker está ejecutándose"
    echo "  2. Verifica que los puertos 3000, 5000, 5037 estén disponibles"
    echo "  3. Intenta: docker-compose logs"
    echo ""
    exit 1
fi

echo ""
echo -e "${YELLOW}[*] Esperando que los servicios estén listos...${NC}"
sleep 8
echo ""

echo -e "${GREEN}"
echo "╔══════════════════════════════════════════════════════════════════════════════╗"
echo "║                    🎉 ¡YAXTUN-OmniCore INICIADO!                              ║"
echo "╠══════════════════════════════════════════════════════════════════════════════╣"
echo "║  🌐 PORTAL WEB:      http://localhost:3000                                    ║"
echo "║  📡 API REST:        http://localhost:5000                                    ║"
echo "║  🔌 ADB SERVER:      localhost:5037                                           ║"
echo "║                                                                                ║"
echo "║  ✨ Abre tu navegador en: http://localhost:3000                              ║"
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo ""

echo -e "${YELLOW}💡 COMANDOS ÚTILES:${NC}"
echo ""
echo "  Ver estado:          docker-compose ps"
echo "  Ver logs:            docker-compose logs -f"
echo "  Detener:             docker-compose down"
echo "  Reiniciar:           docker-compose restart"
echo ""

# Intentar abrir navegador automáticamente
if command -v xdg-open &> /dev/null; then
    xdg-open http://localhost:3000 2>/dev/null &
elif command -v open &> /dev/null; then
    open http://localhost:3000 2>/dev/null &
fi

echo -e "${GREEN}✅ Sistema listo para usar${NC}"
echo ""
