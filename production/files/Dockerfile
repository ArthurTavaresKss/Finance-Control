FROM php:8.3-apache

# Define o timezone como variável de ambiente (usada pelo tzdata)
ENV TZ=America/Sao_Paulo

# Instala dependências do sistema + extensões do PHP
RUN apt-get update && apt-get install -y \
    tzdata \
    libonig-dev \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
    && docker-php-ext-install pdo_mysql mysqli mbstring \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configura o timezone no PHP
RUN echo "date.timezone=America/Sao_Paulo" > /usr/local/etc/php/conf.d/timezone.ini

# Copia os arquivos do projeto
COPY --chown=www-data:www-data ./app /var/www/html

# Define a pasta public como DocumentRoot
# RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf
