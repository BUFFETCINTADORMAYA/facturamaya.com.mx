FROM python:3.10-slim-bullseye

ENV DEBIAN_FRONTEND=noninteractive \
    PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PIP_NO_CACHE_DIR=1 \
    APP_NAME=Yaxtun-OmniCore \
    APP_VERSION=2.0.0

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    android-tools-adb \
    android-tools-fastboot \
    libusb-1.0-0 \
    libusb-1.0-0-dev \
    usbutils \
    libimobiledevice6 \
    libimobiledevice-utils \
    libusbmuxd6 \
    libusbmuxd-tools \
    ideviceinstaller \
    usbmuxd \
    wget \
    curl \
    git \
    build-essential \
    libssl-dev \
    libffi-dev \
    nodejs \
    npm \
    wkhtmltopdf \
    xvfb \
    libfontconfig \
    libx11-6 \
    vim \
    nano \
    htop \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . /app/

RUN pip install --upgrade pip setuptools wheel && \
    pip install -r requirements.txt && \
    mkdir -p /app/backups/{android,ios,components} \
    /app/logs/{android,ios} \
    /app/database/{devices,roms,guides,pdfs} \
    /app/tools/cache && \
    cd /app/tools && \
    wget -q https://dl.google.com/android/repository/platform-tools-latest-linux.zip && \
    unzip -q platform-tools-latest-linux.zip && \
    rm platform-tools-latest-linux.zip && \
    chmod +x platform-tools/adb platform-tools/fastboot

RUN mkdir -p /root/.android && \
    echo 'ANDROID_ADB_SERVER_PORT=5037' >> /root/.bashrc

EXPOSE 3000 5000 5037

COPY docker-entrypoint.sh /app/
RUN chmod +x /app/docker-entrypoint.sh

HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD python -c "import requests; requests.get('http://localhost:5000/health')" || exit 1

VOLUME ["/app/backups", "/app/logs", "/app/database"]

ENTRYPOINT ["/app/docker-entrypoint.sh"]
CMD ["run"]
