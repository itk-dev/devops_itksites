# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.8.6] - 2025-03-11

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

[Unreleased]: https://github.com/itk-dev/devops_itksites/compare/1.6.0...HEAD
[1.6.0]: https://github.com/itk-dev/devops_itksites/compare/1.5.0...1.6.0
[1.5.0]: https://github.com/itk-dev/devops_itksites/compare/1.4.1...1.5.0
[1.4.1]: https://github.com/itk-dev/devops_itksites/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/itk-dev/devops_itksites/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/itk-dev/devops_itksites/compare/1.2.2...1.3.0
[1.2.2]: https://github.com/itk-dev/devops_itksites/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/itk-dev/devops_itksites/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/itk-dev/devops_itksites/compare/1.0.0...1.2.0
[1.0.0]: https://github.com/itk-dev/devops_itksites/releases/tag/1.0.0
