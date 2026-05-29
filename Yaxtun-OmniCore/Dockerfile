"""Yaxtun-OmniCore - Sistema Completo Integrado
Reparación de Android + iPhone con IA, Órdenes de Trabajo, PDF y Comunicaciones"""

FROM python:3.10-slim-bullseye

LABEL maintainer="Yaxtun-OmniCore Team"
LABEL description="Sistema Profesional de Reparación de Dispositivos Móviles"
LABEL version="2.0.0"

ENV DEBIAN_FRONTEND=noninteractive \
    PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PIP_NO_CACHE_DIR=1 \
    APP_NAME=Yaxtun-OmniCore \
    APP_VERSION=2.0.0 \
    LANG=es_MX.UTF-8 \
    LC_ALL=es_MX.UTF-8

# Instalar todas las dependencias del sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Herramientas Android
    android-tools-adb \
    android-tools-fastboot \
    libusb-1.0-0 \
    libusb-1.0-0-dev \
    usbutils \
    # Herramientas iOS
    libimobiledevice6 \
    libimobiledevice-utils \
    libusbmuxd6 \
    libusbmuxd-tools \
    ideviceinstaller \
    usbmuxd \
    # Utilidades generales
    wget \
    curl \
    git \
    build-essential \
    libssl-dev \
    libffi-dev \
    # Node.js para interfaz web
    nodejs \
    npm \
    # PDF y herramientas de documento
    wkhtmltopdf \
    xvfb \
    libfontconfig1 \
    libx11-6 \
    # Editor y monitoreo
    vim \
    nano \
    htop \
    # Utilidades de comunicación
    ca-certificates \
    # Locale
    locales \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && echo "es_MX.UTF-8 UTF-8" > /etc/locale.gen \
    && locale-gen

WORKDIR /app

# Copiar archivos del proyecto
COPY . /app/

# Instalar dependencias Python
RUN pip install --upgrade pip setuptools wheel && \
    pip install -r requirements.txt 2>&1 | grep -v "already satisfied" || true

# Crear directorios de datos persistentes
RUN mkdir -p /app/backups/{android,ios,components} \
    /app/logs/{android,ios} \
    /app/database/{devices,roms,guides,pdfs,templates} \
    /app/tools/cache \
    /app/uploads \
    /app/exports && \
    chmod -R 755 /app/backups /app/logs /app/database /app/tools /app/uploads /app/exports

# Descargar y configurar herramientas externas
RUN cd /app/tools && \
    wget -q https://dl.google.com/android/repository/platform-tools-latest-linux.zip && \
    unzip -q platform-tools-latest-linux.zip && \
    rm platform-tools-latest-linux.zip && \
    chmod +x platform-tools/adb platform-tools/fastboot && \
    echo "ADB y Fastboot descargados correctamente"

# Configurar ADB
RUN mkdir -p /root/.android && \
    echo 'ANDROID_ADB_SERVER_PORT=5037' >> /root/.bashrc && \
    echo 'export PATH="/app/tools/platform-tools:$PATH"' >> /root/.bashrc

# Exponer puertos
EXPOSE 3000 5000 5037 8000

# Script de inicialización
COPY docker-entrypoint.sh /app/
RUN chmod +x /app/docker-entrypoint.sh

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD python -c "import requests; requests.get('http://localhost:5000/health')" || exit 1

# Volúmenes para persistencia
VOLUME ["/app/backups", "/app/logs", "/app/database", "/app/uploads", "/app/exports"]

# Punto de entrada
ENTRYPOINT ["/app/docker-entrypoint.sh"]
CMD ["run"]
