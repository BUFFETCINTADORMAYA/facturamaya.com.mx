#!/bin/bash

# YAXTUN-OmniCore - Descargador Automático para Mac/Linux

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

clear

echo -e "${BLUE}"
echo ""
echo "╔══════════════════════════════════════════════════════════════════════════════╗"
echo "║           🌟 YAXTUN-OmniCore - Descargador Automático             ║"
echo "║     Sistema Profesional de Reparación de Dispositivos Móviles        ║"
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo ""

# Verificar curl
if ! command -v curl &> /dev/null; then
    echo -e "${RED}❌ curl no encontrado${NC}"
    echo ""
    echo "Instala curl:"
    echo "  Mac:   brew install curl"
    echo "  Linux: sudo apt-get install curl"
    echo ""
    exit 1
fi

echo -e "${YELLOW}[*] Verificando herramientas...${NC}"
echo -e "${GREEN}  ✅ curl disponible${NC}"

# Directorio de destino
DEST_DIR="$HOME/Yaxtun-OmniCore"

echo ""
echo -e "${YELLOW}[*] Descargando Yaxtun-OmniCore desde GitHub...${NC}"
echo -e "    Destino: $DEST_DIR"
echo ""

# Crear directorio temporal
TEMP_DIR="/tmp/yaxtun-$$"
mkdir -p "$TEMP_DIR"
cd "$TEMP_DIR"

# Descargar ZIP
echo -e "${YELLOW}[*] Descargando...${NC}"
if curl -L -o yaxtun-download.zip "https://github.com/BUFFETCINTADORMAYA/facturamaya.com.mx/archive/refs/heads/repair-system-dev.zip" 2>/dev/null; then
    echo -e "${GREEN}  ✅ Descarga completada${NC}"
else
    echo -e "${RED}  ❌ Error descargando${NC}"
    echo ""
    echo "Verifica tu conexión a internet"
    rm -rf "$TEMP_DIR"
    exit 1
fi

echo ""
echo -e "${YELLOW}[*] Extrayendo archivos...${NC}"
unzip -q yaxtun-download.zip

if [ $? -ne 0 ]; then
    echo -e "${RED}  ❌ Error extrayendo${NC}"
    rm -rf "$TEMP_DIR"
    exit 1
fi

echo -e "${GREEN}  ✅ Archivos extraídos${NC}"

echo ""
echo -e "${YELLOW}[*] Copiando Yaxtun-OmniCore...${NC}"

# Limpiar destino anterior
rm -rf "$DEST_DIR"
mkdir -p "$DEST_DIR"

# Buscar y copiar carpeta correcta
for dir in facturamaya.com.mx-*/; do
    if [ -d "${dir}Yaxtun-OmniCore" ]; then
        cp -r "${dir}Yaxtun-OmniCore/"* "$DEST_DIR/"
        break
    fi
done

if [ ! -f "$DEST_DIR/START.sh" ]; then
    echo -e "${RED}  ❌ Error: No se encontró Yaxtun-OmniCore${NC}"
    rm -rf "$TEMP_DIR"
    exit 1
fi

echo -e "${GREEN}  ✅ Copia completada${NC}"

echo ""
echo -e "${YELLOW}[*] Limpiando archivos temporales...${NC}"

# Limpiar temporales
cd ~
rm -rf "$TEMP_DIR"

echo -e "${GREEN}  ✅ Limpieza completada${NC}"

echo ""
echo ""
echo -e "${GREEN}"
echo "╔══════════════════════════════════════════════════════════════════════════════╗"
echo "║              🎉 ¡YAXTUN-OmniCore DESCARGADO EXITOSAMENTE!              ║"
echo "╠══════════════════════════════════════════════════════════════════════════════╣"
echo "║  📂 Ubicación: $DEST_DIR                         ║"
echo "║                                                                  ║"
echo "║  🕐 Próximos pasos:                                             ║"
echo "║     1. cd $DEST_DIR                               ║"
echo "║     2. bash START.sh                                             ║"
echo "║     3. Espera 30-60 segundos                                     ║"
echo "║     4. El navegador abre http://localhost:3000 automáticamente  ║"
echo "║                                                                  ║"
echo "║  ✨ ¡El sistema está listo para usar!                             ║"
echo "╚══════════════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo ""

echo -e "${YELLOW}💡 Para iniciar el sistema:\n${NC}"
echo "   cd $DEST_DIR"
echo "   bash START.sh"
echo ""

echo -e "${GREEN}✅ ¡Descarga completada!${NC}"
echo ""
