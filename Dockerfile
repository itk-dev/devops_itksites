FROM dunglas/frankenphp

# add additional extensions here:
RUN install-php-extensions \
	pdo_mysql \
	gd \
	intl \
	zip \
	opcache \
    amqp \
    xdebug

# Install composer
COPY --from=composer/composer:2-bin /composer /usr/bin/composer