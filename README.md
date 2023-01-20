# devops_itksites


# Fixtures

There are not implemented on

* sites
* installations
* domains

This is due to automated processes and scripts that listen from sites and data is therefore not relevant to have. The architecture makes it posible to delete all the above data.

## Development

```sh
docker compose pull
docker compose up --detach
docker compose exec phpfpm composer install
docker compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

Define stuff in `.env.local`:

```dotenv
# .env.local
…

OIDC_CLI_LOGIN_ROUTE=admin

# JSON list of string, e.g '["Digital post", "CPR-opslag"]'
SERVICE_CERTIFICATE_SERVICES='["Digital post", "CPR-opslag"]'
```

```sh
docker compose exec phpfpm bin/console itk-dev:openid-connect:login admin@example.com
```

### Load fixtures

```sh
docker compose exec phpfpm composer fixtures
```
