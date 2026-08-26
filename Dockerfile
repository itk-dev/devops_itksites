# FrankenPHP image for this application, in two flavours.
#
# The published dunglas/frankenphp images are deliberately minimal, so the
# extensions this application cannot boot without are added on top. Everything
# else it needs — ctype, iconv, dom, mbstring, opcache, … — is already there.
#
# Pinned to PHP 8.5 to match itkdev/php8.5-fpm and itkdev/supervisor-php8.5: the
# messenger worker and the web container share vendor/ and var/cache over the
# same bind mount, so they have to agree on the PHP version.
#
# Pick a stage explicitly. Compose does, through `target:` in the two override
# files; a bare `docker build .` gets `prod`, the last stage, which is the safer
# of the two to end up with by accident.
FROM dunglas/frankenphp:1.12-php8.5 AS base

RUN install-php-extensions \
	pdo_mysql \
	amqp \
	intl \
	gd \
	zip

# msmtp keeps sendmail_path working the way it does in itkdev/php8.5-fpm.
RUN apt-get update \
	&& apt-get install --no-install-recommends --yes msmtp \
	&& rm -rf /var/lib/apt/lists/*

COPY --from=composer/composer:2-bin /composer /usr/bin/composer

# The itkdev/php8.5-fpm image turns these into ini settings and defaults them on
# the image rather than in compose. `.docker/php.ini` reads the same names, so
# the same overrides work and no variable is ever unset.
ENV PHP_LOGS=/dev/stderr \
	PHP_TIMEZONE=Europe/Copenhagen \
	PHP_MEMORY_LIMIT=128M \
	PHP_MAX_EXECUTION_TIME=30 \
	PHP_MAX_INPUT_VARS=1000 \
	PHP_POST_MAX_SIZE=8M \
	PHP_UPLOAD_MAX_FILESIZE=2M \
	PHP_SENDMAIL_PATH="/usr/sbin/sendmail -S host.docker.internal -t -i" \
	PHP_OPCACHE_ENABLED=1 \
	PHP_OPCACHE_JIT=off \
	PHP_OPCACHE_MEMORY_CONSUMPTION=64 \
	PHP_OPCACHE_MAX_ACCELERATED_FILES=20000 \
	PHP_OPCACHE_MAX_WASTED_PERCENTAGE=10 \
	PHP_OPCACHE_REVALIDATE_FREQ=0 \
	PHP_OPCACHE_VALIDATE_TIMESTAMPS=1

# Development: Xdebug, and OPcache rechecking files so an edit takes effect.
FROM base AS dev

RUN install-php-extensions xdebug

# Read by .docker/php-dev.ini, which only development mounts.
ENV PHP_XDEBUG_MODE=off \
	PHP_XDEBUG_CLIENT_HOST=host.docker.internal \
	PHP_XDEBUG_START_WITH_REQUEST=yes \
	PHP_XDEBUG_MAX_NESTING_LEVEL=256 \
	PHP_XDEBUG_OUTPUT_DIR=/app

# Production: no Xdebug, and OPcache trusting what it compiled.
#
# validate_timestamps=0 stops PHP stat-ing every file on every request, which
# validate_timestamps=1 with revalidate_freq=0 made it do. The cost is that a
# code change needs a new container — both deployment paths give it one, since
# staging runs `up -d --force-recreate` and the release playbook brings the stack
# up again, and a fresh container starts with an empty OPcache. It also makes
# PHP_OPCACHE_REVALIDATE_FREQ moot.
FROM base AS prod

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
