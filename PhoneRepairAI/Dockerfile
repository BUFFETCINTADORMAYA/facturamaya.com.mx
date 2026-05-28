# PhoneRepairAI - Docker Container
# Sistema completo aislado de reparación de teléfonos
# Android + iOS + Diagnóstico con IA

FROM python:3.10-slim-bullseye

# Establecer variables de entorno
ENV DEBIAN_FRONTEND=noninteractive \
    PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PIP_NO_CACHE_DIR=1

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Android Tools
    android-tools-adb \
    android-tools-fastboot \
    libusb-1.0-0 \
    libusb-1.0-0-dev \
    usbutils \
    # iOS Tools (libimobiledevice)
    libimobiledevice6 \
    libimobiledevice-utils \
    libusbmuxd6 \
    libusbmuxd-tools \
    ideviceinstaller \
    usbmuxd \
    # Herramientas generales
    wget \
    curl \
    git \
    build-essential \
    libssl-dev \
    libffi-dev \
    # NodeJS para interfaz web
    nodejs \
    npm \
    # Utilidades
    vim \
    nano \
    htop \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Crear directorio de trabajo
WORKDIR /app

# Copiar archivos del proyecto
COPY PhoneRepairAI/ /app/
COPY requirements.txt /app/

# Instalar dependencias Python
RUN pip install --upgrade pip setuptools wheel && \
    pip install -r requirements.txt

# Instalar dependencias NPM para interfaz web
RUN cd /app/ui/dashboard && npm install --production || true

# Crear directorios necesarios
RUN mkdir -p /app/backups/{android,ios} \
    /app/logs \
    /app/roms \
    /app/tools \
    /app/database/cache

# Descargar y configurar herramientas externas
RUN echo "Configurando herramientas externas..." && \
    cd /app/tools && \
    # Descargar platform-tools (ADB/Fastboot)
    wget -q https://dl.google.com/android/repository/platform-tools-latest-linux.zip && \
    unzip -q platform-tools-latest-linux.zip && \
    rm platform-tools-latest-linux.zip && \
    chmod +x platform-tools/adb platform-tools/fastboot

# Configurar ADB para aceptar conexiones
RUN mkdir -p /root/.android && \
    echo 'ANDROID_ADB_SERVER_PORT=5037' >> /root/.bashrc

# Exponer puertos
EXPOSE 3000 5000 5037

# Crear script de inicialización
COPY docker-entrypoint.sh /app/
RUN chmod +x /app/docker-entrypoint.sh

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD python -c "import requests; requests.get('http://localhost:5000/health')" || exit 1

# Volúmenes para persistencia
VOLUME ["/app/backups", "/app/logs", "/app/database"]

# Comando de inicio
ENTRYPOINT ["/app/docker-entrypoint.sh"]
CMD ["run"]
