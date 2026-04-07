---
name: create-migration
description: Generate and validate a Doctrine migration after entity changes
model: sonnet
---

After entity changes, generate and validate a Doctrine migration:

1. Run `docker compose exec -T phpfpm bin/console doctrine:migrations:diff` to generate a migration
2. Read the generated migration file and verify the SQL looks correct
3. Run `docker compose exec -T phpfpm bin/console doctrine:migrations:migrate --no-interaction`
4. Run `docker compose exec -T phpfpm bin/console doctrine:schema:validate`

Report the migration file path, the SQL it contains, and whether schema validation passed.
If schema validation fails, investigate and report the discrepancies.
