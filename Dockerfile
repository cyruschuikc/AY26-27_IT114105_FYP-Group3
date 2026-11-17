FROM php:8.2-apache

# The app only needs mysqli (PDO is not used).
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Serve the Web/ directory as the Apache document root.
COPY Web/ /var/www/html/

# Ensure upload directories exist and are writable by Apache (www-data).
# The app also mkdir()s them at runtime, but only if the parent is writable.
RUN mkdir -p /var/www/html/uploads/chat_files /var/www/html/uploads/project_attachments \
    && chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80
