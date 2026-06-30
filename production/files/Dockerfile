FROM php:8.3-apache                              
# Instala dependências do sistema + extensões do PHP                                              RUN apt-get update && apt-get install -y \
    libonig-dev \
    && docker-php-ext-install pdo_mysql mysqli mbstring \
    && a2enmod rewrite \                             && apt-get clean && rm -rf /var/lib/apt/lists/*
                                                 # Copia os arquivos do projeto
COPY --chown=www-data:www-data ./app /var/www/html

# Define a pasta public como DocumentRoot
# RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf
