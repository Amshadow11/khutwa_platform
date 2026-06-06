FROM khutwacodex-php-worker-base:latest

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        chromium \
        fontconfig \
        fonts-noto-core \
        nodejs \
        npm \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

CMD ["php", "artisan", "queue:work", "redis", "--queue=pdf", "--sleep=3", "--tries=2", "--timeout=180"]
