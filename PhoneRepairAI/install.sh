#!/bin/bash

# PhoneRepairAI - Script de Instalación
# Instala el sistema completo en un contenedor Docker

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║       PhoneRepairAI - Script de Instalación                  ║"
echo "║    Sistema Completo Aislado en Contenedor Docker             ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Verificar si Docker está instalado
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker no está instalado${NC}"
    echo -e "${YELLOW}Instálalo desde: https://www.docker.com/products/docker-desktop${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Docker encontrado${NC}"

# Verificar si Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}❌ Docker Compose no está instalado${NC}"
    echo -e "${YELLOW}Instálalo desde: https://docs.docker.com/compose/install${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Docker Compose encontrado${NC}"

# Verificar versiones
echo -e "${YELLOW}[*] Versiones detectadas:${NC}"
echo -e "  Docker: $(docker --version)"
echo -e "  Compose: $(docker-compose --version)"

# Crear directorios necesarios
echo -e "${YELLOW}[*] Creando directorios...${NC}"
mkdir -p backups/{android,ios}
mkdir -p logs/{android,ios}
mkdir -p database/{devices,roms}
echo -e "${GREEN}✅ Directorios creados${NC}"

# Build de imagen Docker
echo -e "${YELLOW}[*] Construyendo imagen Docker...${NC}"
echo -e "${BLUE}Esto puede tomar unos minutos...${NC}"

if docker-compose build; then
    echo -e "${GREEN}✅ Imagen construida exitosamente${NC}"
else
    echo -e "${RED}❌ Error construyendo imagen${NC}"
    exit 1
fi

# Crear volúmenes
echo -e "${YELLOW}[*] Creando volúmenes Docker...${NC}"
docker volume create phonerepairal-backups || true
docker volume create phonerepairal-logs || true
docker volume create phonerepairal-database || true
docker volume create phonerepairal-roms || true
echo -e "${GREEN}✅ Volúmenes creados${NC}"

# Iniciar contenedor
echo -e "${YELLOW}[*] Iniciando contenedor...${NC}"

if docker-compose up -d; then
    echo -e "${GREEN}✅ Contenedor iniciado${NC}"
else
    echo -e "${RED}❌ Error iniciando contenedor${NC}"
    exit 1
fi

# Esperar a que el sistema esté listo
echo -e "${YELLOW}[*] Esperando a que el sistema inicie...${NC}"
sleep 10

# Verificar que esté corriendo
if docker-compose ps | grep -q "phonerepairal-system.*Up"; then
    echo -e "${GREEN}✅ Sistema en ejecución${NC}"
else
    echo -e "${RED}❌ Error: Sistema no está ejecutándose${NC}"
    docker-compose logs
    exit 1
fi

# Información de acceso
echo -e "${GREEN}"
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║          🎉 PhoneRepairAI Instalado Exitosamente!            ║"
echo "╠══════════════════════════════════════════════════════════════╣"
echo "║  🌐 Web Dashboard:  http://localhost:3000                    ║"
echo "║  📡 API REST:       http://localhost:5000                    ║"
echo "║  🔌 ADB Server:     localhost:5037                           ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${YELLOW}[*] Comandos útiles:${NC}"
echo ""
echo "  Ver estado:           docker-compose ps"
echo "  Ver logs:             docker-compose logs -f"
echo "  Acceder a shell:      docker-compose exec phonerepairal bash"
echo "  Detener sistema:      docker-compose down"
echo "  Reiniciar:            docker-compose restart"
echo ""
echo -e "${YELLOW}[*] Para conectar un teléfono:${NC}"
echo ""
echo "  1. Conecta tu dispositivo vía USB"
echo "  2. Abre el dashboard: http://localhost:3000"
echo "  3. El sistema lo detectará automáticamente"
echo ""

echo -e "${GREEN}✅ ¡Instalación completada!${NC}"
