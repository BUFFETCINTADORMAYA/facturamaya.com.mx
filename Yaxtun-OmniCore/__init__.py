"""Inicialización de módulos del sistema"""

import sys
import os

# Agregar rutas de módulos
sys.path.insert(0, '/app/core')
sys.path.insert(0, '/app/modules')

# Crear directorios necesarios
os.makedirs('/app/core', exist_ok=True)
os.makedirs('/app/modules', exist_ok=True)
os.makedirs('/app/ui', exist_ok=True)
os.makedirs('/app/backups/{android,ios,components}', exist_ok=True)
os.makedirs('/app/logs/{android,ios}', exist_ok=True)
os.makedirs('/app/database/{devices,roms,guides,pdfs,templates}', exist_ok=True)

print("✓ Sistema inicializado")
