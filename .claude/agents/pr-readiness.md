---
name: pr-readiness
description: Run all CI-equivalent checks locally before creating a PR
model: haiku
---

Run the following checks in sequence inside Docker and report results for each.
Stop early if a critical check fails.

Never load fixtures or drop databases without `--env=test`: the dev database
holds data that cannot be rebuilt.

## Checks

1. **Composer validate**: `docker compose exec -T phpfpm composer validate --strict`
2. **Composer normalize**: `docker compose exec -T phpfpm composer normalize --dry-run`
3. **PHP coding standards**: `docker compose exec -T phpfpm composer coding-standards-check`
4. **PHPStan**: `docker compose exec -T phpfpm vendor/bin/phpstan analyse --no-progress`
5. **Rector**: `docker compose exec -T phpfpm vendor/bin/rector process --dry-run --no-progress-bar`
6. **PHPUnit tests**: `docker compose exec -T phpfpm composer tests`
7. **Doctrine schema**: `docker compose exec -T phpfpm bin/console --env=test doctrine:schema:validate`
8. **Fixtures**: `docker compose exec -T phpfpm bin/console --env=test hautelook:fixtures:load --no-interaction`
9. **Twig coding standards**: `docker compose exec -T phpfpm vendor/bin/twig-cs-fixer lint templates/`
10. **Markdown**: `docker compose run --rm -T markdownlint markdownlint '**/*.md'`
11. **YAML, JS and styles**: `docker compose run --rm -T prettier '**/*.{yml,yaml}' 'assets/**/*.{js,css,scss}' --check --no-error-on-unmatched-pattern`
12. **Asset build**: `docker compose run --rm -T node yarn build`
13. **EasyAdmin skill up to date**: `docker compose exec -T phpfpm bin/console easyadmin:ai:update --check`
14. **API spec up to date**: Run `docker compose exec -T phpfpm composer update-api-spec`, then check `git diff --exit-code public/api-spec-v1.*`
15. **CHANGELOG updated**: Verify CHANGELOG.md has changes compared to the base branch (`git diff develop -- CHANGELOG.md`)

## Output

Report a summary table with columns: Check Name, Status (pass/fail), and error output for failures.
