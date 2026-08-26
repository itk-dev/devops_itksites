---
name: update-api-spec
description: Regenerate and stage API spec files after API resource changes
user-invocable: true
---

When API resources or operations change, regenerate the OpenAPI spec files:

1. Run `docker compose exec -T phpfpm composer update-api-spec`
2. Check `git diff public/api-spec-v1.*` for changes
3. If changed, stage the spec files with `git add public/api-spec-v1.yaml public/api-spec-v1.json`
4. Report what changed in the API spec
