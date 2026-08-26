# FrankenPHP POC.
#
# The published dunglas/frankenphp images are deliberately minimal, so the
# extensions this application cannot boot without are added on top. Everything
# else it needs — ctype, iconv, dom, mbstring, opcache, … — is already in the
# base image.
# Pinned to PHP 8.5 to match itkdev/php8.5-fpm and itkdev/supervisor-php8.5:
# the messenger worker and the web container share vendor/ and var/cache over
# the same bind mount, so they have to agree on the PHP version.
FROM dunglas/frankenphp:1.12-php8.5

RUN install-php-extensions \
	pdo_mysql \
	amqp \
	intl \
	gd \
	zip \
	xdebug

# msmtp keeps sendmail_path working the way it does in itkdev/php8.5-fpm.
RUN apt-get update \
	&& apt-get install --no-install-recommends --yes msmtp \
	&& rm -rf /var/lib/apt/lists/*

COPY --from=composer/composer:2-bin /composer /usr/bin/composer
