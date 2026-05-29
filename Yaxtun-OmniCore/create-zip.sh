#!/bin/bash
# Script para crear ZIP descargable de Yaxtun-OmniCore

echo "Creando ZIP de Yaxtun-OmniCore..."

# Crear ZIP sin los directorios de runtime
zip -r Yaxtun-OmniCore-v2.0.0.zip Yaxtun-OmniCore/ \
  -x "Yaxtun-OmniCore/backups/*" \
  -x "Yaxtun-OmniCore/logs/*" \
  -x "Yaxtun-OmniCore/database/*" \
  -x "Yaxtun-OmniCore/.git/*" \
  -x "Yaxtun-OmniCore/__pycache__/*" \
  -x "Yaxtun-OmniCore/*.pyc"

echo "✅ ZIP creado: Yaxtun-OmniCore-v2.0.0.zip"
ls -lh Yaxtun-OmniCore-v2.0.0.zip
