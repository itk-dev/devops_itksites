# DevOps - ITKsites

This is our internal server and site registration tool. It works in tandem with our
[ITK sites server harvester](https://github.com/itk-dev/devops_itkServerHarvest).
The harvester is installed by default on all servers, and runs at intervals and collects
information about sites and installations running on the server. These are sent as
`DetectionResults` to ITKsites where they are analysed and processed.

This allows us to monitor
* What is installed and running
* Which sites/domains we are hosting
* What docker images we are running
* What packages and modules we are running
* If there are known CVE's for the packages/modules
* What git repositories we are hosting

Additionally we can register and document
* All OpenID Connect setups
* All Services Certificates

Servers, OpenID Connect setups, Services Certificates must be created and maintained manually.
All other information is kept up to date by analysing the DetectionResults.

## Architecture
This is a Symfony 6 project build with api-platform 3.x and EasyAdmin.

Api-platform provides a simple REST api for POST'ing the DetectionResults.
These are then processed asynchronously by a series of message handlers.

EasyAdmin is used to provide an interface to view and search the analyzed data,
as well as editing the data that must updated manually.

The system is build so that all analyzed data can be truncated safely and rebuild
by "replaying" the DetectionResults. This means that care must be taken when
manually maintained data and auto updated data must have cross references.

## Development

```sh
docker compose pull
docker compose up --detach
docker compose exec phpfpm composer install
docker compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

Then create a `.env.local` file to set secrets for your local setup.

### OpenID Connect
All users access is controlled by OpenID Connect. For local development you must
add the following to your `.env.local` file:

```dotenv
###> itk-dev/openid-connect-bundle ###
AZURE_AZ_OIDC_METADATA_URL=<value>
AZURE_AZ_OIDC_CLIENT_ID=<value>
AZURE_AZ_OIDC_CLIENT_SECRET=<value>
AZURE_AZ_OIDC_REDIRECT_URI=https://itksites.local.itkdev.dk/openid-connect/generic
###< itk-dev/openid-connect-bundle ###
```

### Fixtures

There are not implemented on

* sites
* installations
* domains

This is due to automated processes and scripts that listen from sites and data
is therefore not relevant to have. The architecture makes it possible to delete
all the above data.

#### Load fixtures

```sh
docker compose exec phpfpm composer fixtures
```

After loading fixtures you can sign in as an admin user:

```sh
docker compose exec phpfpm bin/console itk-dev:openid-connect:login admin@example.com
```

### Job queues and handlers

All processing of Detctionresults is done in a series of message handlers. To
run these do either:
```shell
docker compose exec phpfpm composer queues
```

or
```shell
docker compose exec phpfpm bin/console messenger:consume async --failure-limit=1 -vvv
```

### Assets

We use [Webpack Encore](https://symfony.com/doc/current/frontend.html#webpack-encore) to build assets:

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
