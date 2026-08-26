# DevOps - ITKsites

[![Woodpecker](https://img.shields.io/badge/woodpecker-prod|stg-blue.svg?style=flat-square&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMiIgaGVpZ2h0PSIyMiI+PHBhdGggZmlsbD0iI2ZmZiIgZD0iTTEuMjYzIDIuNzQ0QzIuNDEgMy44MzIgMi44NDUgNC45MzIgNC4xMTggNS4wOGwuMDM2LjAwN2MtLjU4OC42MDYtMS4wOSAxLjQwMi0xLjQ0MyAyLjQyMy0uMzggMS4wOTYtLjQ4OCAyLjI4NS0uNjE0IDMuNjU5LS4xOSAyLjA0Ni0uNDAxIDQuMzY0LTEuNTU2IDcuMjY5LTIuNDg2IDYuMjU4LTEuMTIgMTEuNjMuMzMyIDE3LjMxNy42NjQgMi42MDQgMS4zNDggNS4yOTcgMS42NDIgOC4xMDdhLjg1Ny44NTcgMCAwMC42MzMuNzQ0Ljg2Ljg2IDAgMDAuOTIyLS4zMjNjLjIyNy0uMzEzLjUyNC0uNzk3Ljg2LTEuNDI0Ljg0IDMuMzIzIDEuMzU1IDYuMTMgMS43ODMgOC42OTdhLjg2Ni44NjYgMCAwMDEuNTE3LjQxYzIuODgtMy40NjMgMy43NjMtOC42MzYgMi4xODQtMTIuNjc0LjQ1OS0yLjQzMyAxLjQwMi00LjQ1IDIuMzk4LTYuNTgzLjUzNi0xLjE1IDEuMDgtMi4zMTggMS41NS0zLjU2Ni4yMjgtLjA4NC41NjktLjMxNC43OS0uNDQxbDEuNzA3LS45ODEtLjI1NiAxLjA1MmEuODY0Ljg2NCAwIDAwMS42NzguNDA4bC42OC0yLjg1OCAxLjI4NS0yLjk1YS44NjMuODYzIDAgMTAtMS41ODEtLjY4N2wtMS4xNTIgMi42NjktMi4zODMgMS4zNzJhMTguOTcgMTguOTcgMCAwMC41MDgtMi45ODFjLjQzMi00Ljg2LS43MTgtOS4wNzQtMy4wNjYtMTEuMjY2LS4xNjMtLjE1Ny0uMjA4LS4yODEtLjI0Ny0uMjYuMDk1LS4xMi4yNDktLjI2LjM1OC0uMzc0IDIuMjgzLTEuNjkzIDYuMDQ3LS4xNDcgOC4zMTkuNzUuNTg5LjIzMi44NzYtLjMzNy4zMTYtLjY3LTEuOTUtMS4xNTMtNS45NDgtNC4xOTYtOC4xODgtNi4xOTMtLjMxMy0uMjc1LS41MjctLjYwNy0uODktLjkxM0M5LjgyNS41NTUgNC4wNzIgMy4wNTcgMS4zNTUgMi41NjljLS4xMDItLjAxOC0uMTY2LjEwMy0uMDkyLjE3NW0xMC45OCA1Ljg5OWMtLjA2IDEuMjQyLS42MDMgMS44LTEgMi4yMDgtLjIxNy4yMjQtLjQyNi40MzYtLjUyNC43MzgtLjIzNi43MTQuMDA4IDEuNTEuNjYgMi4xNDMgMS45NzQgMS44NCAyLjkyNSA1LjUyNyAyLjUzOCA5Ljg2LS4yOTEgMy4yODgtMS40NDggNS43NjMtMi42NzEgOC4zODUtMS4wMzEgMi4yMDctMi4wOTYgNC40ODktMi41NzcgNy4yNTlhLjg1My44NTMgMCAwMC4wNTYuNDhjMS4wMiAyLjQzNCAxLjEzNSA2LjE5Ny0uNjcyIDkuNDZhOTYuNTg2IDk2LjU4NiAwIDAwLTEuOTctOC43MTFjMS45NjQtNC40ODggNC4yMDMtMTEuNzUgMi45MTktMTcuNjY4LS4zMjUtMS40OTctMS4zMDQtMy4yNzYtMi4zODctNC4yMDctLjIwOC0uMTgtLjQwMi0uMjM3LS40OTUtLjE2Ny0uMDg0LjA2LS4xNTEuMjM4LS4wNjIuNDQ0LjU1IDEuMjY2Ljg3OSAyLjU5OSAxLjIyNiA0LjI3NiAxLjEyNSA1LjQ0My0uOTU2IDEyLjQ5LTIuODM1IDE2Ljc4MmwtLjExNi4yNTktLjQ1Ny45ODJjLS4zNTYtMi4wMTQtLjg1LTMuOTUtMS4zMy01Ljg0LTEuMzgtNS40MDYtMi42OC0xMC41MTUtLjQwMS0xNi4yNTQgMS4yNDctMy4xMzcgMS40ODMtNS42OTIgMS42NzItNy43NDYuMTE2LTEuMjYzLjIxNi0yLjM1NS41MjYtMy4yNTIuOTA1LTIuNjA1IDMuMDYyLTMuMTc4IDQuNzQ0LTIuODUyIDEuNjMyLjMxNiAzLjI0IDEuNTkzIDMuMTU2IDMuNDJ6bS0yLjg2OC42MmExLjE3NyAxLjE3NyAwIDEwLjczNi0yLjIzNiAxLjE3OCAxLjE3OCAwIDEwLS43MzYgMi4yMzd6Ii8+PC9zdmc+Cg==)](https://woodpecker.itkdev.dk/repos/2)
[![GitHub Release](https://img.shields.io/github/v/release/itk-dev/devops_itksites?style=flat-square&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0NDggNTEyIj48IS0tIUZvbnQgQXdlc29tZSBGcmVlIDYuNy4yIGJ5IEBmb250YXdlc29tZSAtIGh0dHBzOi8vZm9udGF3ZXNvbWUuY29tIExpY2Vuc2UgLSBodHRwczovL2ZvbnRhd2Vzb21lLmNvbS9saWNlbnNlL2ZyZWUgQ29weXJpZ2h0IDIwMjUgRm9udGljb25zLCBJbmMuLS0+PHBhdGggZmlsbD0iI2ZmZiIgZD0iTTAgODBMMCAyMjkuNWMwIDE3IDYuNyAzMy4zIDE4LjcgNDUuM2wxNzYgMTc2YzI1IDI1IDY1LjUgMjUgOTAuNSAwTDQxOC43IDMxNy4zYzI1LTI1IDI1LTY1LjUgMC05MC41bC0xNzYtMTc2Yy0xMi0xMi0yOC4zLTE4LjctNDUuMy0xOC43TDQ4IDMyQzIxLjUgMzIgMCA1My41IDAgODB6bTExMiAzMmEzMiAzMiAwIDEgMSAwIDY0IDMyIDMyIDAgMSAxIDAtNjR6Ii8+PC9zdmc+)](https://github.com/itk-dev/devops_itksites/releases)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/itk-dev/devops_itksites/pr.yaml?style=flat-square&logo=github)](https://github.com/itk-dev/devops_itksites/actions/workflows/pr.yaml)
[![Codecov](https://img.shields.io/codecov/c/github/itk-dev/devops_itksites?style=flat-square&logo=codecov)](https://codecov.io/gh/itk-dev/devops_itksites)
[![GitHub last commit](https://img.shields.io/github/last-commit/itk-dev/devops_itksites?style=flat-square)](https://github.com/itk-dev/devops_itksites/commits/develop/)
[![GitHub License](https://img.shields.io/github/license/itk-dev/devops_itksites?style=flat-square)](https://github.com/itk-dev/devops_itksites/blob/develop/LICENSE)
[![claude.md](https://img.shields.io/badge/%F0%9F%A4%96_claude.md-AI%20ready-8A2BE2?style=flat-square)](https://github.com/itk-dev/devops_itksites/blob/develop/claude.md)

This is our internal server and site registration tool. It works in tandem with our
[ITK sites server harvester](https://github.com/itk-dev/devops_itkServerHarvest).
The harvester is installed by default on all servers, and runs at intervals and collects
information about sites and installations running on the server. These are sent as
`DetectionResults` to ITKsites where they are analysed and processed.

This allows us to monitor

- What is installed and running
- Which sites/domains we are hosting
- What docker images we are running
- What packages and modules we are running
- If there are known CVE's for the packages/modules
- What git repositories we are hosting

Additionally we can register and document

- All OpenID Connect setups
- All Services Certificates

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

## API

Authenticated users can access a simple read-only API – see the API documentation on `/api/docs` for details.

### API keys

Run the `app:user:set-api-key` console command to set the API for a user:

``` shell
docker compose exec frankenphp php bin/console app:user:set-api-key <user-id>
```

Use the API key to make an authenticated request, e.g.

``` shell
curl --header 'accept: application/json' --header 'authorization: Apikey <the API key>' https://itksites.local.itkdev.dk/api/sites
```

## Health checks

Three endpoints report on the application, in increasing order of detail:

| Endpoint | Access | Checks |
| --- | --- | --- |
| `/health/live` | Public | Nothing – only that the app responds |
| `/health/ready` | Public | All checks, aggregated status only |
| `/health/detail` | `ITKBasicAuth` in Traefik | Per-check results and timings |

`/health/ready` answers `200` when everything is well and `503` when it is not.
It deliberately does not say *what* is wrong – point monitoring at this one and
read `/health/detail` when it goes red:

``` shell
curl --silent https://itksites.local.itkdev.dk/health/detail | jq
```

The checks cover the database, the RabbitMQ messenger transport and the
freshness of the most recent detection result. The last one catches an ingest
pipeline that has stopped while the application itself is still serving
requests.

`HEALTH_INGEST_MAX_AGE` sets how old the most recent detection result may be
before ingest is reported as degraded.

Results are cached for `HEALTH_CACHE_TTL` seconds so that polling does not turn
into load on the dependencies. The cache is the dedicated, filesystem-backed
`cache.health` pool in `config/packages/cache.yaml` – it has to keep working
while the database and the broker are down, and the adapter can be swapped
there without touching code.

`^/health` is excluded from the Symfony firewalls: both user providers are
Doctrine entity providers, so an authenticated endpoint would fail to
authenticate during a database outage and answer `500` rather than reporting
that the database is down.

## Development

```sh
docker compose pull
docker compose up --detach
docker compose exec frankenphp composer install
docker compose exec frankenphp bin/console doctrine:migrations:migrate --no-interaction
```

Then create a `.env.local` file to set secrets for your local setup.

### Web server

The site is served by a single [FrankenPHP](https://frankenphp.dev) container
instead of the usual phpfpm and nginx pair. `docker-compose.override.yml`
locally, and `docker-compose.server.override.yml` on the servers, add the
`frankenphp` service and park `phpfpm` and `nginx` in a profile that is never
enabled, so neither starts. Commands that used to run against `phpfpm` run
against `frankenphp`.

Traefik still terminates TLS. Caddy listens on plain HTTP on port 8080 and
`auto_https` is off, so it neither requests nor serves certificates.

Two files carry the configuration that used to live on the phpfpm and nginx
images:

- `.docker/Caddyfile` – a port of `.docker/nginx.conf` and
  `.docker/templates/default.conf.template`.
- `.docker/php.ini` – the PHP settings the `itkdev/php8.5-fpm` image derives
  from its `PHP_*` environment variables, plus its tuned baseline. The compose
  files still set the same variables; the ini file interpolates them.

Coming from the phpfpm stack, remove the containers it left behind once:

```sh
docker compose rm --stop --force phpfpm nginx
```

#### Logging

php-fpm sent its error log, its slowlog and everything a worker wrote to stderr
to `/dev/stderr`, and configured no access log at all – the nginx access log was
the only per-request record. `.docker/php.ini` keeps `error_log` pointed at
`${PHP_LOGS}` and Caddy's access log replaces nginx's.

Caddy logs JSON rather than nginx's `log_format main` text. Every field that
format carried is in it – `client_ip`, `user_id`, `ts`, method, uri, proto,
`status`, `size` and the `Referer`, `User-Agent` and `X-Forwarded-For` headers –
plus `duration`, which nginx did not log. The text layout cannot be reproduced
byte for byte without the Caddy transform encoder, which the published image
does not carry: this build has `console`, `json`, `append`, `filter` and
`journald`. JSON also matches supercronic, which the fpm image already runs with
`-json`.

#### Worker mode

Worker mode is off. Turning it on needs no PHP package and no code change:
`symfony/runtime` has shipped `FrankenPhpWorkerRunner` since 7.4, FrankenPHP
sets `FRANKENPHP_WORKER=1` for a worker script, and `SymfonyRuntime::getRunner()`
switches on that. `.docker/Caddyfile` reads `{$FRANKENPHP_CONFIG}`, so the switch
is an environment variable on the `frankenphp` service:

```yaml
environment:
    FRANKENPHP_CONFIG: worker /app/public/index.php
```

Add a count – `worker /app/public/index.php 8` – to override the default, which
is twice the number of CPU cores. Keep `num_threads` × `memory_limit` below the
memory available to the container.

What it bought here, measured on this project in `prod` with a warm OPcache:
about 20% more requests per second on `/admin` and half the median latency,
against about 40% *fewer* on `/health/live`. The trivial endpoint is worker
mode's worst case – there is no per-request work for the saved kernel boot to be
weighed against, and the runner's `gc_collect_cycles()` on every request is not
free. The numbers come from a laptop sharing CPU with other containers and
running the application over a bind mount, so treat them as a shape rather than
a figure, and measure again on a server before adopting.

**Services must not carry request state.** Under php-fpm a service instance died
with the request; in a worker it does not, so anything a service remembers leaks
into the next request. Prefer keeping services stateless. Where state is
deliberate, implement `Symfony\Contracts\Service\ResetInterface` –
`autoconfigure` tags it `kernel.reset` and Symfony calls it between requests.
For an object you do not own, clear it at the call site, the way every
`AdminUrlGenerator` chain here opens with `unsetAll()`.

That rule is enforced. [igor-php](https://github.com/igor-php/igor-php) audits
every shared service in the compiled container for state that would leak between
requests, and runs on every pull request:

```sh
docker compose exec frankenphp composer worker-state-check
```

Existing findings live in `igor-baseline.json`, so the job fails only on new
ones. Every entry there carries a reason – most are Doctrine entities returned
from a repository, which igor reads as shared services, and `AdminUrlGenerator`
chains it cannot see are already cleared by `unsetAll()`. Read the reasons before
adding to them; if a finding is genuine, fix it rather than baseline it. After a
deliberate change, regenerate with `composer worker-state-baseline` and write a
reason for each new entry.

The audit needs the service map that `IgorPhpBundle` writes during
`cache:clear`, so run that first if the cache is cold. Vendor code is out of
scope (`ignore_vendors` in `igor.json`): it reported 341 findings there, none of
them ours to fix.

`FRANKENPHP_RESET_KERNEL=1`, on Symfony 8.1 and later, clones the kernel between
requests instead. It hides this class of bug at the cost of a boot per request,
which is most of what worker mode is for – useful to compare against, not to
depend on.

#### Metrics

`/metrics` serves Prometheus metrics from Caddy, behind the `ITKMetricsAuth@file`
middleware on its own Traefik router.

This is where `/cron-metrics` used to point. That route proxied to supercronic
on `${NGINX_CRON_METRICS}`, and the fpm entrypoint only starts supercronic when
`/app/crontab` exists – this project has no crontab, so nothing ever listened
and the route answered `502`. nginx exported nothing itself: `stub_status` is
compiled into the image but the template never enabled it, and php-fpm's
`pm.status_path = /status` was never routed.

Caddy does export, so the endpoint has something behind it: request counts,
durations and sizes by code, method and handler, requests in flight, and Go
runtime and process metrics. FrankenPHP's own thread metrics only appear in
worker mode, which is off. A supercronic sidecar, if one is added, needs a route
of its own.

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

> [!NOTE]
> In the `dev` environment the main firewall security is disabled
> (`security.yaml` → `when@dev`), so authentication is not required.
> This is because the current AAK OIDC setup doesn't support `itksites.local.itkdev.dk`.

### Fixtures

There are not implemented on

- sites
- installations
- domains

This is due to automated processes and scripts that listen from sites and data
is therefore not relevant to have. The architecture makes it possible to delete
all the above data.

#### Load fixtures

```sh
docker compose exec frankenphp composer fixtures
```

After loading fixtures you can sign in as an admin user:

```sh
docker compose exec frankenphp bin/console itk-dev:openid-connect:login admin@example.com
```

### Job queues and handlers

All processing of Detctionresults is done in a series of message handlers. To
run these do either:

```shell
docker compose exec frankenphp composer queues
```

or

```shell
docker compose exec frankenphp bin/console messenger:consume async --failure-limit=1 -vvv
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

### 🤖 AI coding agents

This project includes an [`claude.md`](claude.md) file that provides project
context for Claude Code. The file describes the project architecture,
technology stack, development commands, CI/CD setup, and coding conventions.

Tool-specific configuration (permissions, hooks, plugins) lives in `.claude/`
and is not portable across tools.

> [!NOTE]
> `agents.md` is a vendor-neutral standard supported by tools such as
> [OpenCode](https://opencode.ai/) and others. Claude Code doesn't currently support
> `agents.md`, `claude.md` should be renamed to a vendor neutral standard when Claude supports it.

#### Claude Code plugins

The following plugins are enabled in `.claude/settings.json`:

| Plugin              | Purpose                                                                     | Source                                                                       |
| ------------------- | --------------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| `php-lsp`           | PHP language server for type-aware code intelligence                        | [claude-plugins-official](https://github.com/anthropics/claude-code-plugins) |
| `context7`          | Up-to-date documentation lookup for Symfony, Doctrine, API Platform, etc.   | [claude-plugins-official](https://github.com/anthropics/claude-code-plugins) |
| `code-review`       | Pull request code review                                                    | [claude-plugins-official](https://github.com/anthropics/claude-code-plugins) |
| `code-simplifier`   | Suggests clarity and maintainability improvements                           | [claude-plugins-official](https://github.com/anthropics/claude-code-plugins) |
| `security-guidance` | Flags potential security issues (OWASP, injection, etc.)                    | [claude-plugins-official](https://github.com/anthropics/claude-code-plugins) |
| `playwright`        | Browser automation for debugging and testing the EasyAdmin UI               | [claude-plugins-official](https://github.com/anthropics/claude-code-plugins) |
| `feature-dev`       | Guided feature development with codebase exploration and architecture focus | [claude-plugins-official](https://github.com/anthropics/claude-code-plugins) |

> **Note:** The `php-lsp` plugin requires [Intelephense](https://intelephense.com/)
> installed globally: `npm install -g intelephense`. All other plugins work
> without additional dependencies.

#### Claude Code agents

Custom agents in `.claude/agents/` automate multi-step workflows:

| Agent              | Purpose                                                           |
| ------------------ | ----------------------------------------------------------------- |
| `pr-readiness`     | Runs all CI-equivalent checks locally before creating a PR        |
| `create-migration` | Generates and validates a Doctrine migration after entity changes |

#### Claude Code skills

Custom skills in `.claude/skills/` provide repeatable task shortcuts:

| Skill             | Invocation         | Purpose                                               |
| ----------------- | ------------------ | ----------------------------------------------------- |
| `update-api-spec` | `/update-api-spec` | Regenerate and stage OpenAPI spec files after changes |

#### Claude Code hooks

Hooks in `.claude/settings.json` run automatically on tool events:

| Hook           | Trigger        | Purpose                                                |
| -------------- | -------------- | ------------------------------------------------------ |
| Docker start   | `SessionStart` | Starts Docker services on session start                |
| PHP-CS-Fixer   | `PostToolUse`  | Auto-formats PHP files on edit                         |
| PHPStan        | `PostToolUse`  | Runs static analysis on edited PHP files               |
| Twig-CS-Fixer  | `PostToolUse`  | Auto-formats Twig templates on edit                    |
| Composer norm  | `PostToolUse`  | Normalizes `composer.json` on edit                     |
| Prettier       | `PostToolUse`  | Auto-formats JS, CSS, YAML, and Markdown files on edit |
| Lock guard     | `PreToolUse`   | Blocks edits to lock files and `.env.local`            |
| Container lint | `Stop`         | Validates Symfony DI container before stopping         |

#### MCP servers

A shared `.mcp.json` provides team-wide MCP server configuration:

| Server     | Purpose                                                                   |
| ---------- | ------------------------------------------------------------------------- |
| `context7` | Live documentation lookup for Symfony, Doctrine, API Platform, and others |
