---
name: pr-readiness
description: Run all CI-equivalent checks locally before creating a PR
model: haiku
---

Run the following checks in sequence inside Docker and report results for each.
Stop early if a critical check fails.

## Checks

1. **Composer validate**: `docker compose exec -T phpfpm composer validate --strict`
2. **Composer normalize**: `docker compose exec -T phpfpm composer normalize --dry-run`
3. **PHP coding standards**: `docker compose exec -T phpfpm composer coding-standards-check`
4. **PHPStan**: `docker compose exec -T phpfpm vendor/bin/phpstan analyse --no-progress`
5. **PHPUnit tests**: `docker compose exec -T phpfpm composer tests`
6. **Twig coding standards**: `docker compose exec -T phpfpm vendor/bin/twig-cs-fixer lint templates/`
7. **JS coding standards**: `docker compose run --rm -T node yarn coding-standards-check`
8. **API spec up to date**: Run `docker compose exec -T phpfpm composer update-api-spec`, then check `git diff --exit-code public/api-spec-v1.*`
9. **CHANGELOG updated**: Verify CHANGELOG.md has changes compared to the base branch (`git diff develop -- CHANGELOG.md`)

## Output

Report a summary table with columns: Check Name, Status (pass/fail), and error output for failures.
