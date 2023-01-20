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

### Load fixtures

```sh
docker compose exec phpfpm composer fixtures
```

After loading fixtures you can sign in as an admin user:

```sh
docker compose exec phpfpm bin/console itk-dev:openid-connect:login admin@example.com
```

## Assets

We use [Webpack
Encore]()https://symfony.com/doc/current/frontend.html#webpack-encore) to build
assets:

```sh
docker compose run --rm node yarn install
docker compose run --rm node yarn build
```

Use

```sh
docker compose run --rm node yarn watch
```

during development to automatically rebuild assets when source files change.

### Coding standards

```sh
docker compose run --rm node yarn coding-standards-check
```
