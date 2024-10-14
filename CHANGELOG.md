# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
