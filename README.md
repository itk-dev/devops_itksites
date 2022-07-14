# devops_itksites

## Installation
```sh
docker-compose up -d
docker-compose exec phpfpm composer install
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```