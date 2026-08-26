# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- [#59](https://github.com/itk-dev/devops_itksites/pull/59)
  4544: POC for using FrankenPHP behind Traefik
  - Serve the site from a single FrankenPHP container. The `frankenphp` service
    is added in `docker-compose.override.yml` and
    `docker-compose.server.override.yml`; `phpfpm` and `nginx` move into a
    profile that is never enabled
  - Port the nginx configuration to `.docker/Caddyfile` and the PHP settings the
    fpm image took from `PHP_*` environment variables to `.docker/php.ini`
  - Traefik keeps terminating TLS: `auto_https` is off and Caddy serves plain
    HTTP on 8080
  - Move the whole stack to PHP 8.5, `itkdev/php8.5-fpm` and
    `itkdev/supervisor-php8.5` included
  - Point Taskfile, workflows, Woodpecker and the README at the `frankenphp`
    service
  - Worker mode stays off: `runtime/frankenphp-symfony` has no Symfony 8 release
- [#96](https://github.com/itk-dev/devops_itksites/pull/96)
  Show the Service Agreements monthly price as Danish kroner,
  `12.500,50 kr.`, on index and detail
- [#95](https://github.com/itk-dev/devops_itksites/pull/95)
  Update `vincentlanglet/twig-cs-fixer` to 4.0. Every other dependency is
  already at its latest minor; the remaining majors are held back by their
  dependents
- [#94](https://github.com/itk-dev/devops_itksites/pull/94)
  Use EasyAdmin's own components in the admin templates
  - Replace hand-rolled badge and icon markup with `<twig:ea:Badge>` and
    `<twig:ea:Icon>`, so the admin follows EasyAdmin's theming
  - Drop the unused `AutoBadgeMenuItem`/`AutoBadgeCrudMenuItem` pair: EasyAdmin
    hides a badge whose content is null
  - Set the ITK blue with the theme API instead of overriding EasyAdmin's
    colour variables one by one
  - Load the admin stylesheet again: it was added as `css/admin.css`, a file
    deleted in #81, so every admin page carried a 404 and no ITK styling
- [#93](https://github.com/itk-dev/devops_itksites/pull/93)
  Update composer dependencies, clearing 15 security advisories
  - `api-platform/core` 4.3.7 → 4.3.17, `easycorp/easyadmin-bundle` 5.0.11 → 5.5.1,
    `guzzlehttp/guzzle` 7.10.6 → 7.15.5, `guzzlehttp/psr7` 2.10.4 → 2.13.1
  - Regenerated the API spec: `symfony/yaml` now writes sequence items on their
    own line. No API changes
- [#83](https://github.com/itk-dev/devops_itksites/pull/83) 7523: Service agreements
  - Add Project entity top-level Economics project.
  - Add CodeOwner entity
  - Add Leantime integration
- [#80](https://github.com/itk-dev/devops_itksites/pull/80  ) 5566: Service agreements
- [#91](https://github.com/itk-dev/devops_itksites/pull/91) Health endpoints
  - Add `/health/live`, `/health/ready` and `/health/detail` endpoints
  - Add health checks for database, RabbitMQ transport and detection result freshness
  - Cache check results in a dedicated `cache.health` pool
  - Exclude `^/health` from the firewalls and protect `/health/detail` with `ITKBasicAuth`
- [#90](https://github.com/itk-dev/devops_itksites/pull/90)
  - Fixed user API key migration failing on databases with more than one user
  - Generated an API key for existing users, as users created since already get
  - Added users to the fixtures and a CI job running migrations on a populated
    database
- [#89](https://github.com/itk-dev/devops_itksites/pull/89)
  Added `--rm` to `docker compose run` in prod deployment
- [#88](https://github.com/itk-dev/devops_itksites/pull/88)
  - Let users use the API
  - Add security to detection results API endpoint
  - Add server and site collections API endpoints
- [#80](https://github.com/itk-dev/devops_itksites/pull/80) 5566: Service agreements
  - Add security contract entity with crud controller
  - Add Abstract full crud controller and extend on it in some cases
  - Add economics service and sync action/command for service agreement synchronization
- [#81](https://github.com/itk-dev/devops_itksites/pull/81) 5564: Asset Mapper migration
  - Add Symfony Asset Mapper bundle and importmap
- Add Renovate auto-patch + auto-release pipeline (Phase 1 fork validation)
- [#87](https://github.com/itk-dev/devops_itksites/pull/87) Update `codecov/codecov-action` to v7

## [1.11.2] - 2026-06-02

- [#85](https://github.com/itk-dev/devops_itksites/pull/85)
  Update openid-connect-bundle to 5.0 and Symfony to 8.1
  - Bump `itk-dev/openid-connect-bundle` to `^5.0` (pulls `itk-dev/openid-connect` 5.0)
  - Migrate OIDC authenticator to the new exception marker interface
    `OpenIdConnectExceptionInterface` (5.0 BC: concrete exceptions no longer
    extend `ItkOpenIdConnectException`)
  - Update Symfony to 8.1

## [1.11.1] - 2026-06-01

- [#84](https://github.com/itk-dev/devops_itksites/pull/84)
  Update composer dependencies. Fix for Symfony and Twig CVE's

## [1.11.0] - 2026-05-19

- [#78](https://github.com/itk-dev/devops_itksites/pull/78)
  Update composer dependencies, fix php-cs-fixer deprecation
- [#77](https://github.com/itk-dev/devops_itksites/pull/77)
  Fix SemverFilter: respect value2 with directional operators
- [#76](https://github.com/itk-dev/devops_itksites/pull/76)
  Add server type filter and sort on Installation, Site, Domain
- [#75](https://github.com/itk-dev/devops_itksites/pull/75)
  Add semver-aware filter on every admin version column, make version
  column semver sortable

## [1.10.1] - 2026-05-11

- [#71](https://github.com/itk-dev/devops_itksites/pull/71)
  Update composer dependencies

## [1.10.0] - 2026-04-23

- [#68](https://github.com/itk-dev/devops_itksites/pull/68)
  6667: Update advisories on Detailed site display

- [#68](https://github.com/itk-dev/devops_itksites/pull/68)
  6667: Show sites affected on advisories.

## [1.9.2] - 2026-04-07

- [#67](https://github.com/itk-dev/devops_itksites/pull/67)
  6654: Fix `#[AdminRoute] attribute error, add smoke tests for admin routes
-

## [1.9.1] - 2026-04-07

- [#66](https://github.com/itk-dev/devops_itksites/pull/66)
  6654: Downgrade to PHP 8.4, update composer dependencies

## [1.9.0] - 2026-04-07

- [#64](https://github.com/itk-dev/devops_itksites/pull/64)
  6869: Improve OpenAPI spec with descriptions, examples, and error codes
- [#63](https://github.com/itk-dev/devops_itksites/pull/63)
  6654: Upgrade to PHP 8.5, Symfony 8.0, EasyAdmin 5.x, DoctrineBundle 3.x, PHPUnit 13
- [#62](https://github.com/itk-dev/devops_itksites/pull/62)
  6869: Add claude.md and Claude Code configuration for AI coding agents
- [#58](https://github.com/itk-dev/devops_itksites/pull/58)
  5002: Added export to everything

## [1.8.10] - 2025-07-02

- Fix deprecation warning for "erase credentials"

## [1.8.9] - 2025-07-01

- Update to Symfony 7.3, update composer dependencies
- Fix dependency injection for ExportCrudControllerTrait
- Split config in dev/prod for secrets to only use vault in production

## [1.8.8] - 2025-05-12

- Show server actions inline
- Update composer dependencies

## [1.8.7] - 2025-03-11

- Fix secrets naming in woodpecker file

## [1.8.6] - 2025-03-10

- Update woodpecker config with labels and STG deploy

## [1.8.5] - 2025-02-14

- Change to `composer/semver` to fix advisories mapping

## [1.8.4] - 2025-02-14

- Fix server edit/create bug
- Updated dependencies and api spec

## [1.8.3] - 2025-02-06

- Fix missing "depends" in docker compose
- Fix easyadmin deprecations

## [1.8.2] - 2025-02-06

- Add 'application/ld+json' as allowed format

## [1.8.1] - 2025-02-06

- Increase memory for supervisor container

## [1.8.0] - 2025-02-06

- Upgrade to PHP 8.4
- Upgrade to: Symfony 7.2, Doctrine ORM 3.x / DBAL 4.x, Api-platform 4.0, PhpUnit 11 with dependencies
- Switch to PHPStan
- Added cleanup for detection results
- Refactor rootDir normalization to ensure values are always normalized, fix type errors,
- Fix various values not being set

## [1.7.1] - 2024-11-08

- Added automatic deployment

## [1.7.0] - 2024-10-14

- Switch to using vault bundle
- Upgraded to Symfony 6.4

## [1.6.1] - 2024-06-18

- Updated composer setup
- Added new GPU hosts and Hetzner

## [1.6.0] - 2024-01-16

- [#43](https://github.com/itk-dev/devops_itksites/pull/43)
  Added CSV export
- [#42](https://github.com/itk-dev/devops_itksites/pull/42)
  Add and apply CS fixer rule to enforce strict types on all files.
- [#44](https://github.com/itk-dev/devops_itksites/pull/44)
  Added notes on OIDC

## [1.5.0] - 2023-09-20

- [#40](https://github.com/itk-dev/devops_itksites/pull/40)
  Update to Symfony 6.3. Update dependencies.
- [#39](https://github.com/itk-dev/devops_itksites/pull/39)
  Added OIDC description to Readme, added server type field to OIDC.
- [#38](https://github.com/itk-dev/devops_itksites/pull/38)
  Added "rootDir" normalizer to ensure they match between different types of DetectionResults. Fixes missing sites and domains.

## [1.4.1] - 2023-08-04

- [#36](https://github.com/itk-dev/devops_itksites/pull/36)
  Implemented OIDC code flow and handled target path after login.

## [1.4.0] - 2023-08-01

- [#34](https://github.com/itk-dev/devops_itksites/pull/34)
  Updated properties on OIDC and cleaned up.

## [1.3.0] - 2023-07-27

- Added advisory handler and UI
- Fixed "Integrity constraint violation: 1062 Duplicate entry" errors
- Minor UI styling updates
- Added logo and favicon

## [1.2.2] - 2023-05-26

- Added Debian 11 to OS selections

## [1.2.1] - 2023-05-25

- Fixed hostname for rabbit in docker compose

## [1.2.0] - 2023-05-25

- [#32](https://github.com/itk-dev/devops_itksites/pull/32)
  Refactored message handling to enable async processing
- [#31](https://github.com/itk-dev/devops_itksites/pull/31)
  Updated to API Platform 3.1, updated Symfony
- [#23](https://github.com/itk-dev/devops_itksites/pull/23)
  Service certificates

## [1.0.0] - 2022-09-15

[Unreleased]: https://github.com/itk-dev/devops_itksites/compare/1.11.2...HEAD
[1.11.2]: https://github.com/itk-dev/devops_itksites/compare/1.11.1...1.11.2
[1.11.1]: https://github.com/itk-dev/devops_itksites/compare/1.11.0...1.11.1
[1.11.0]: https://github.com/itk-dev/devops_itksites/compare/1.10.1...1.11.0
[1.10.1]: https://github.com/itk-dev/devops_itksites/compare/1.10.0...1.10.1
[1.10.0]: https://github.com/itk-dev/devops_itksites/compare/1.9.2...1.10.0
[1.9.2]: https://github.com/itk-dev/devops_itksites/compare/1.9.1...1.9.2
[1.9.1]: https://github.com/itk-dev/devops_itksites/compare/1.9.0...1.9.1
[1.9.0]: https://github.com/itk-dev/devops_itksites/compare/1.8.9...1.9.0
[1.8.10]: https://github.com/itk-dev/devops_itksites/compare/1.8.9...1.8.10
[1.8.9]: https://github.com/itk-dev/devops_itksites/compare/1.8.8...1.8.9
[1.8.8]: https://github.com/itk-dev/devops_itksites/compare/1.8.7...1.8.8
[1.8.7]: https://github.com/itk-dev/devops_itksites/compare/1.8.6...1.8.7
[1.8.6]: https://github.com/itk-dev/devops_itksites/compare/1.8.5...1.8.6
[1.8.5]: https://github.com/itk-dev/devops_itksites/compare/1.8.4...1.8.5
[1.8.4]: https://github.com/itk-dev/devops_itksites/compare/1.8.3...1.8.4
[1.8.3]: https://github.com/itk-dev/devops_itksites/compare/1.8.2...1.8.3
[1.8.2]: https://github.com/itk-dev/devops_itksites/compare/1.8.1...1.8.2
[1.8.1]: https://github.com/itk-dev/devops_itksites/compare/1.8.0...1.8.1
[1.8.0]: https://github.com/itk-dev/devops_itksites/compare/1.7.1...1.8.0
[1.7.1]: https://github.com/itk-dev/devops_itksites/compare/1.7.0...1.7.1
[1.7.0]: https://github.com/itk-dev/devops_itksites/compare/1.6.1...1.7.0
[1.6.1]: https://github.com/itk-dev/devops_itksites/compare/1.6.0...1.6.1
[1.6.0]: https://github.com/itk-dev/devops_itksites/compare/1.5.0...1.6.0
[1.5.0]: https://github.com/itk-dev/devops_itksites/compare/1.4.1...1.5.0
[1.4.1]: https://github.com/itk-dev/devops_itksites/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/itk-dev/devops_itksites/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/itk-dev/devops_itksites/compare/1.2.2...1.3.0
[1.2.2]: https://github.com/itk-dev/devops_itksites/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/itk-dev/devops_itksites/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/itk-dev/devops_itksites/compare/1.0.0...1.2.0
[1.0.0]: https://github.com/itk-dev/devops_itksites/releases/tag/1.0.0
