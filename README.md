# DevOps - ITKsites

[![Woodpecker](https://img.shields.io/badge/woodpecker-prod|stg-blue.svg?style=flat-square&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMiIgaGVpZ2h0PSIyMiI+PHBhdGggZmlsbD0iI2ZmZiIgZD0iTTEuMjYzIDIuNzQ0QzIuNDEgMy44MzIgMi44NDUgNC45MzIgNC4xMTggNS4wOGwuMDM2LjAwN2MtLjU4OC42MDYtMS4wOSAxLjQwMi0xLjQ0MyAyLjQyMy0uMzggMS4wOTYtLjQ4OCAyLjI4NS0uNjE0IDMuNjU5LS4xOSAyLjA0Ni0uNDAxIDQuMzY0LTEuNTU2IDcuMjY5LTIuNDg2IDYuMjU4LTEuMTIgMTEuNjMuMzMyIDE3LjMxNy42NjQgMi42MDQgMS4zNDggNS4yOTcgMS42NDIgOC4xMDdhLjg1Ny44NTcgMCAwMC42MzMuNzQ0Ljg2Ljg2IDAgMDAuOTIyLS4zMjNjLjIyNy0uMzEzLjUyNC0uNzk3Ljg2LTEuNDI0Ljg0IDMuMzIzIDEuMzU1IDYuMTMgMS43ODMgOC42OTdhLjg2Ni44NjYgMCAwMDEuNTE3LjQxYzIuODgtMy40NjMgMy43NjMtOC42MzYgMi4xODQtMTIuNjc0LjQ1OS0yLjQzMyAxLjQwMi00LjQ1IDIuMzk4LTYuNTgzLjUzNi0xLjE1IDEuMDgtMi4zMTggMS41NS0zLjU2Ni4yMjgtLjA4NC41NjktLjMxNC43OS0uNDQxbDEuNzA3LS45ODEtLjI1NiAxLjA1MmEuODY0Ljg2NCAwIDAwMS42NzguNDA4bC42OC0yLjg1OCAxLjI4NS0yLjk1YS44NjMuODYzIDAgMTAtMS41ODEtLjY4N2wtMS4xNTIgMi42NjktMi4zODMgMS4zNzJhMTguOTcgMTguOTcgMCAwMC41MDgtMi45ODFjLjQzMi00Ljg2LS43MTgtOS4wNzQtMy4wNjYtMTEuMjY2LS4xNjMtLjE1Ny0uMjA4LS4yODEtLjI0Ny0uMjYuMDk1LS4xMi4yNDktLjI2LjM1OC0uMzc0IDIuMjgzLTEuNjkzIDYuMDQ3LS4xNDcgOC4zMTkuNzUuNTg5LjIzMi44NzYtLjMzNy4zMTYtLjY3LTEuOTUtMS4xNTMtNS45NDgtNC4xOTYtOC4xODgtNi4xOTMtLjMxMy0uMjc1LS41MjctLjYwNy0uODktLjkxM0M5LjgyNS41NTUgNC4wNzIgMy4wNTcgMS4zNTUgMi41NjljLS4xMDItLjAxOC0uMTY2LjEwMy0uMDkyLjE3NW0xMC45OCA1Ljg5OWMtLjA2IDEuMjQyLS42MDMgMS44LTEgMi4yMDgtLjIxNy4yMjQtLjQyNi40MzYtLjUyNC43MzgtLjIzNi43MTQuMDA4IDEuNTEuNjYgMi4xNDMgMS45NzQgMS44NCAyLjkyNSA1LjUyNyAyLjUzOCA5Ljg2LS4yOTEgMy4yODgtMS40NDggNS43NjMtMi42NzEgOC4zODUtMS4wMzEgMi4yMDctMi4wOTYgNC40ODktMi41NzcgNy4yNTlhLjg1My44NTMgMCAwMC4wNTYuNDhjMS4wMiAyLjQzNCAxLjEzNSA2LjE5Ny0uNjcyIDkuNDZhOTYuNTg2IDk2LjU4NiAwIDAwLTEuOTctOC43MTFjMS45NjQtNC40ODggNC4yMDMtMTEuNzUgMi45MTktMTcuNjY4LS4zMjUtMS40OTctMS4zMDQtMy4yNzYtMi4zODctNC4yMDctLjIwOC0uMTgtLjQwMi0uMjM3LS40OTUtLjE2Ny0uMDg0LjA2LS4xNTEuMjM4LS4wNjIuNDQ0LjU1IDEuMjY2Ljg3OSAyLjU5OSAxLjIyNiA0LjI3NiAxLjEyNSA1LjQ0My0uOTU2IDEyLjQ5LTIuODM1IDE2Ljc4MmwtLjExNi4yNTktLjQ1Ny45ODJjLS4zNTYtMi4wMTQtLjg1LTMuOTUtMS4zMy01Ljg0LTEuMzgtNS40MDYtMi42OC0xMC41MTUtLjQwMS0xNi4yNTQgMS4yNDctMy4xMzcgMS40ODMtNS42OTIgMS42NzItNy43NDYuMTE2LTEuMjYzLjIxNi0yLjM1NS41MjYtMy4yNTIuOTA1LTIuNjA1IDMuMDYyLTMuMTc4IDQuNzQ0LTIuODUyIDEuNjMyLjMxNiAzLjI0IDEuNTkzIDMuMTU2IDMuNDJ6bS0yLjg2OC42MmExLjE3NyAxLjE3NyAwIDEwLjczNi0yLjIzNiAxLjE3OCAxLjE3OCAwIDEwLS43MzYgMi4yMzd6Ii8+PC9zdmc+Cg==)](https://woodpecker.itkdev.dk/repos/2)
[![GitHub Release](https://img.shields.io/github/v/release/itk-dev/devops_itksites?style=flat-square&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0NDggNTEyIj48IS0tIUZvbnQgQXdlc29tZSBGcmVlIDYuNy4yIGJ5IEBmb250YXdlc29tZSAtIGh0dHBzOi8vZm9udGF3ZXNvbWUuY29tIExpY2Vuc2UgLSBodHRwczovL2ZvbnRhd2Vzb21lLmNvbS9saWNlbnNlL2ZyZWUgQ29weXJpZ2h0IDIwMjUgRm9udGljb25zLCBJbmMuLS0+PHBhdGggZmlsbD0iI2ZmZiIgZD0iTTAgODBMMCAyMjkuNWMwIDE3IDYuNyAzMy4zIDE4LjcgNDUuM2wxNzYgMTc2YzI1IDI1IDY1LjUgMjUgOTAuNSAwTDQxOC43IDMxNy4zYzI1LTI1IDI1LTY1LjUgMC05MC41bC0xNzYtMTc2Yy0xMi0xMi0yOC4zLTE4LjctNDUuMy0xOC43TDQ4IDMyQzIxLjUgMzIgMCA1My41IDAgODB6bTExMiAzMmEzMiAzMiAwIDEgMSAwIDY0IDMyIDMyIDAgMSAxIDAtNjR6Ii8+PC9zdmc+)](https://github.com/itk-dev/devops_itksites/releases)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/itk-dev/devops_itksites/pr.yaml?style=flat-square&logo=github)](https://github.com/itk-dev/devops_itksites/actions/workflows/pr.yaml)
[![Codecov](https://img.shields.io/codecov/c/github/itk-dev/devops_itksites?style=flat-square&logo=codecov)](https://codecov.io/gh/itk-dev/devops_itksites)
[![GitHub last commit](https://img.shields.io/github/last-commit/itk-dev/devops_itksites?style=flat-square)](https://github.com/itk-dev/devops_itksites/commits/develop/)
[![GitHub License](https://img.shields.io/github/license/itk-dev/devops_itksites?style=flat-square)](https://github.com/itk-dev/devops_itksites/blob/develop/LICENSE)


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
