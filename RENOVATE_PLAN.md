# Plan: Renovate auto-patch + auto-release — fork test, then upstream

## Objective

Validate a Renovate + auto-release pipeline on the **fork** (`turegjorup/devops_itksites`), then re-implement the proven setup on **upstream** (`itk-dev/devops_itksites`).

When complete, the pipeline will:

1. Open grouped patch-update PRs (Composer/npm/GitHub Actions/Docker) **against `main` as hotfixes**, run the existing `pr.yaml` checks, and **auto-merge** when green.
2. Open security-advisory PRs (the `composer audit` set) against `main` with **0-day soak** so they ship as soon as CI passes.
3. After each merge to `main`, promote the `[Unreleased]` section of `CHANGELOG.md` to the next **patch** version, tag from `main`, let `github_build_release.yml` build the GitHub Release, then **back-merge `main` → `develop`** so the branches stay in sync.
4. Continue to route **minor/major** updates against `develop` with Dependency Dashboard approval (no auto-merge).

## Why a fork test first

- The pipeline writes commits, opens PRs, creates tags, publishes releases, and force-syncs `main` ↔ `develop`. Each of those is observable to the upstream team and to anyone subscribed to upstream releases.
- The repo already has a real git-flow with `hotfix/*`, `release/*` and `main`/`develop`. Renovate needs to slot into it cleanly — any misconfiguration produces tag noise, spurious PRs, or broken back-merges that are painful to clean up in a shared repo.
- The fork is a near-identical copy of upstream (same `pr.yaml`, same `changelog.yaml`, same `github_build_release.yml`, same composer.lock), so behavior should transfer 1:1 with only secret/owner substitutions.

## Branch state (verified)

| Branch | Origin (fork) | Upstream | HEAD |
| --- | --- | --- | --- |
| `develop` | ✅ | ✅ | `614c76e` (fork is ahead with renovate lock-file maintenance PRs) |
| `main` | ✅ (just pushed) | ✅ | `4402754` (Merge `release/1.11.0`) |

Highest semver tag on upstream: **`1.11.0`**. First auto-release on the fork should produce `1.11.1`.

## Context (why this is worth doing now)

Running `composer audit` against `composer.lock` flags the May 20, 2026 coordinated Symfony + Twig disclosure:

| Package | Locked | Fixed in |
| --- | --- | --- |
| `symfony/http-kernel`, `security-http`, `runtime`, etc. (v8.0.x) | v8.0.8–8.0.11 | **8.0.12** |
| `twig/twig` | v3.25.0 | **3.26.0** |

Relevant CVEs: `CVE-2026-45075`, `CVE-2026-45065`, `CVE-2026-46626`, `CVE-2026-46633`, `CVE-2026-24425`, plus the HtmlSanitizer / Mailer / Notifier batch. All patch-level — exactly the class this pipeline auto-merges.

## Architecture decisions (apply to both fork and upstream)

- **Self-hosted Renovate** via `renovatebot/github-action` on a schedule. Self-hosted is required because `postUpgradeTasks` (the bit that writes `CHANGELOG.md` in the same commit as the dep bump) is disabled on Mend's hosted app.
- **Two base branches.** `baseBranches: ["main", "develop"]`. Patches/security/digest/lockFileMaintenance route to `main`; minor/major route to `develop`. `matchBaseBranches` in `packageRules` enforces the split.
- **Patch-only auto-merge.** Minor and major bumps open PRs against `develop` but require Dependency Dashboard approval. `php` package never auto-bumps.
- **CHANGELOG gate.** The repo's `changelog.yaml` workflow fails any PR that doesn't touch `CHANGELOG.md`. Rather than fork that workflow, Renovate appends an `[Unreleased]` bullet via `postUpgradeTasks` so every Renovate PR satisfies the gate.
- **Two PATs** so pushes re-trigger Actions (the default `GITHUB_TOKEN` doesn't): `RENOVATE_TOKEN` (Renovate's pushes to `renovate/*` branches) and `RELEASE_TOKEN` (the tag push that fires `github_build_release.yml`, plus the back-merge to `develop`).
- **Soak time:** 7 days for normal patches, 0 days for security advisories. The conservative window catches yanked releases and follow-up advisories; CVE-flagged patches still ship immediately via the vuln-alert path.

## Deliverables (same files in both phases)

```
renovate.json
.github/workflows/renovate.yaml
.github/workflows/auto-release.yaml
.github/scripts/renovate-changelog.sh
```

---

# Phase 1 — Validate on the fork (`turegjorup/devops_itksites`)

Goal: run the full pipeline against the fork's own `main` and `develop` until a clean Renovate PR → auto-merge → tag → GitHub Release → back-merge cycle completes end-to-end, twice (once for a regular patch, once for a security advisory).

## Step 1.1 — Branch & baseline (already partly done)

`main` has been fetched from upstream and pushed to origin (`4402754`). Now:

```bash
git checkout develop && git pull origin develop
git checkout -b feat/renovate-auto-patch
```

Confirm no existing `renovate.json`, `.renovaterc*`, or `dependabot.yml` on develop. If `dependabot.yml` exists, delete it in this PR — Renovate replaces it.

## Step 1.2 — Create `renovate.json` at repo root

Key sections:

- `extends`: `config:recommended`, `:dependencyDashboard`, `:semanticCommitsDisabled`, `:maintainLockFilesWeekly`, `schedule:weekdays`.
- `baseBranches: ["main", "develop"]`, `timezone: "Europe/Copenhagen"`.
- `vulnerabilityAlerts`: `automerge: true`, `minimumReleaseAge: "0 days"`, `prPriority: 10`, `commitMessagePrefix: "security:"`, `matchBaseBranches: ["main"]`. Also enable `osvVulnerabilityAlerts: true`. Security advisories always land on `main` so they ship as a hotfix release.
- `lockFileMaintenance`: weekly Monday morning, auto-merge, `matchBaseBranches: ["main"]`.
- `packageRules`:
  - **Patch/pin/digest/lockFileMaintenance on `main`** → `matchBaseBranches: ["main"]`, `matchUpdateTypes: ["patch", "pin", "digest", "lockFileMaintenance"]`, `automerge: true`, `minimumReleaseAge: "7 days"`. The hotfix path.
  - **Minor/major on `develop`** → `matchBaseBranches: ["develop"]`, `matchUpdateTypes: ["minor", "major"]`, `dependencyDashboardApproval: true`, `automerge: false`. The normal release path.
  - **Suppression rules** so updates don't open against the wrong base:
    - `matchBaseBranches: ["main"]` + `matchUpdateTypes: ["minor", "major"]` → `enabled: false`.
    - `matchBaseBranches: ["develop"]` + `matchUpdateTypes: ["patch", "pin", "digest", "lockFileMaintenance"]` → `enabled: false` (patches reach develop via the back-merge).
  - Group `^symfony/`, `^doctrine/`, `^api-platform/` patches each into one PR (apply on `main`).
  - `php` package → `matchBaseBranches: ["develop"]`, `rangeStrategy: "in-range-only"`, no auto-merge.
  - GitHub Actions → pin digests, auto-merge digest/patch on `main`.
- `postUpgradeTasks`: `bash .github/scripts/renovate-changelog.sh {{{branchName}}} {{{prTitle}}}`, `fileFilters: ["CHANGELOG.md"]`, `executionMode: "branch"`.

## Step 1.3 — Create `.github/scripts/renovate-changelog.sh`

Idempotent script that appends one bullet under `## [Unreleased]`:

- `set -euo pipefail`.
- Exit 0 if `CHANGELOG.md` is already staged or modified in the working tree (prevents duplicate bullets when `postUpgradeTasks` fires multiple times for a grouped PR).
- Python heredoc splices `- Renovate: <prTitle> (\`<branchName>\`)` into the `[Unreleased]` section. If no `[Unreleased]` section exists, create one after the intro paragraph.

```bash
chmod +x .github/scripts/renovate-changelog.sh
```

Local validation:

```bash
.github/scripts/renovate-changelog.sh renovate/symfony-patch "Update symfony (patch)"
git diff CHANGELOG.md   # expect one new bullet under [Unreleased]
git checkout -- CHANGELOG.md
```

## Step 1.4 — Create `.github/workflows/renovate.yaml`

- Triggers: `schedule` (weekdays 05:00 UTC) + `workflow_dispatch` with `logLevel` and `dryRun` inputs.
- `concurrency.group: renovate`, `cancel-in-progress: false`.
- Set up PHP 8.4 + Composer (needed so `composer update` works inside Renovate's container).
- `renovatebot/github-action@v43` with `token: ${{ secrets.RENOVATE_TOKEN }}`.
- Critical env vars for `postUpgradeTasks`:
  - `RENOVATE_ALLOWED_POST_UPGRADE_COMMANDS: '["^bash \\.github/scripts/renovate-changelog\\.sh"]'`
  - `RENOVATE_ALLOW_POST_UPGRADE_COMMAND_TEMPLATING: "true"`
- `RENOVATE_REPOSITORIES: ${{ github.repository }}` — this means the same workflow file works unmodified on both fork and upstream; it always operates on its own repo.

## Step 1.5 — Create `.github/workflows/auto-release.yaml`

Trigger: `pull_request.closed` on `main`, gated by `github.event.pull_request.merged == true && github.event.pull_request.user.login == 'renovate[bot]'`.

Steps:

1. `actions/checkout@v6` with `ref: main`, `fetch-depth: 0`, `token: ${{ secrets.RELEASE_TOKEN }}`.
2. `lewagon/wait-on-check-action@v1.4.0` waiting on the merge commit's required checks (`PHP Unit tests`, `PHPStan`, `Validate Doctrine Schema`, `Load fixtures`, `Build assets`).
3. Compute next patch: `git tag -l '[0-9]*.[0-9]*.[0-9]*' --sort=-v:refname | head -1`, bump the patch component. On the fork, the first run produces `1.11.1`.
4. Gate: if `[Unreleased]` is empty (`awk` extraction returns nothing), `skip=true` and stop.
5. Promote `[Unreleased]` → `[<next>] - <YYYY-MM-DD>` via Python heredoc.
6. `git commit -m "release: <ver>"`, `git tag -a <ver>`, push both to `main`. `github_build_release.yml` fires on the `*.*.*` tag.
7. **Back-merge `main` → `develop`:**
   - `git fetch origin develop && git checkout develop`
   - `git merge --no-ff main -m "chore: back-merge hotfix <ver> into develop"`
   - On conflict: abort, then `gh pr create --base develop --head main --title "Back-merge hotfix <ver>"`. Do not force-push.
   - Otherwise push develop with `RELEASE_TOKEN`.

## Step 1.6 — Local validation before pushing

```bash
python3 -c "import json; json.load(open('renovate.json'))"
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/renovate.yaml'))"
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/auto-release.yaml'))"
bash -n .github/scripts/renovate-changelog.sh
```

If Taskfile defines them, also:

```bash
task lint
```

## Step 1.7 — Fork-side manual setup

Done by **you (turegjorup)** as the fork owner before the pipeline runs:

1. **Repository secrets on `turegjorup/devops_itksites`:**
   - `RENOVATE_TOKEN` — a PAT on your account scoped to the fork with `contents:write`, `pull-requests:write`, `workflows`. For the test phase this can be a classic PAT or fine-grained PAT; a GitHub App is overkill.
   - `RELEASE_TOKEN` — PAT with `contents:write`, `workflows` for the tag push + back-merge.
2. **Branch protection on `main` and `develop` (fork)** — match what you'll later configure on upstream:
   - Required status checks: `PHP Unit tests`, `PHPStan`, `Validate Doctrine Schema`, `Load fixtures`, `Build assets`, `Changelog`, `composer-validate`, `composer-normalized`, `composer-audit`.
   - Require branches up to date before merging.
   - **Allow auto-merge** at the repo level.
   - Allow `RELEASE_TOKEN` identity to direct-push (for back-merges); fallback to PR if blocked.
3. **(Optional) CODEOWNERS exemption** for `renovate[bot]` if you mirror upstream's CODEOWNERS.

## Step 1.8 — Commit & open PR against the fork's `develop`

```bash
git add renovate.json .github/workflows/renovate.yaml .github/workflows/auto-release.yaml .github/scripts/renovate-changelog.sh
# changelog.yaml will gate this PR — add a bullet to [Unreleased]
$EDITOR CHANGELOG.md
git add CHANGELOG.md
git commit -m "Add Renovate auto-patch + auto-release pipeline"
git push -u origin feat/renovate-auto-patch
gh pr create --repo turegjorup/devops_itksites --base develop --fill
```

Merge it once the existing `pr.yaml` checks pass. (No release fires from this — it lands on `develop`, not `main`.)

## Step 1.9 — Promote the pipeline files to `main` on the fork

The pipeline only takes effect for hotfix PRs once the workflow files and `renovate.json` exist on `main`. Two options:

- **Quick:** cherry-pick the four pipeline files from develop to main with a manual PR (`gh pr create --base main --head feat/renovate-pipeline-main`). Safe because these are tooling files; no app behavior changes.
- **Slow:** wait for the next `release/*` → `main` cut to carry them across.

Use the quick path for the fork test so the validation cycle isn't blocked on a release.

## Step 1.10 — First-run verification on the fork

1. **Dry-run.** Actions → Renovate → Run workflow with `dryRun: full`, `logLevel: debug`. Confirm logs show:
   - Config parses cleanly.
   - Patch candidates for `symfony/*` (8.0.8/9/10/11 → 8.0.12) and `twig/twig` (3.25.0 → 3.26.0) are scoped to `main`.
   - Minor/major candidates (if any) scoped to `develop`.
   - `postUpgradeTasks` listed as allowed.
2. **Live run.** Re-run with `dryRun: null`. Dependency Dashboard issue appears; first PRs open within minutes.
3. **First patch PR (against `main`):**
   - `CHANGELOG.md` has exactly one new bullet.
   - `pr.yaml`, `composer-audit`, `Changelog` all pass.
   - PR auto-merges after `minimumReleaseAge` (security PRs immediately, regular patches after 7 days — use a security PR for the first test to skip the soak).
4. **Auto-release cycle:**
   - `auto-release.yaml` runs after merge, computes `1.11.1`, promotes `[Unreleased]`, tags from `main`, pushes.
   - `github_build_release.yml` builds the GitHub Release on the fork.
5. **Back-merge:** verify the release commit reached `develop` (push or PR).

## Step 1.11 — Exit criteria for Phase 1

Move to Phase 2 only when **all of these** are observed on the fork:

- One **security** PR (e.g. a Symfony 8.0.x patch) → auto-merged with 0-day soak → released → back-merged.
- One **regular patch** PR → auto-merged after 7-day soak → released → back-merged. (To avoid blocking the Phase 1 sign-off on a real 7-day wait, temporarily set `minimumReleaseAge: "0 days"` for this single test run, confirm the merge fires, then restore `"7 days"` before going to upstream.)
- One **grouped patch** PR (Symfony or Doctrine cluster) → auto-merged correctly without partial-update artifacts.
- One **minor or major** candidate appeared on the Dependency Dashboard against `develop` and did **not** auto-merge.
- A **back-merge conflict** scenario has been exercised at least once (synthesize one if it doesn't happen naturally) and the PR-fallback worked.
- The CHANGELOG ended each cycle correctly: `[Unreleased]` empty, `[1.11.x] - <date>` populated.

Document any deviations or required tweaks in `RENOVATE_TEST_NOTES.md` (created during this phase, not promoted to upstream).

## Step 1.12 — Cleanup before Phase 2

After the fork test is signed off:

- Optionally delete the test releases (`1.11.1`, `1.11.2`, …) from the fork's Releases page to keep the fork's tag history tidy. The tags themselves can stay.
- Keep the `Renovate` Actions workflow enabled on the fork so it continues to track patches as a low-stakes early-warning system.

---

# Phase 2 — Re-implement on upstream (`itk-dev/devops_itksites`)

Goal: port the validated files to upstream with the minimum possible delta. The smaller the diff between fork and upstream, the cheaper the review and the lower the risk.

## Step 2.1 — Sync fork's pipeline state to a fresh branch

```bash
git fetch upstream develop
git checkout -b feat/renovate-auto-patch upstream/develop
git checkout feat/renovate-auto-patch -- \
  renovate.json \
  .github/workflows/renovate.yaml \
  .github/workflows/auto-release.yaml \
  .github/scripts/renovate-changelog.sh
# OR, if working from the merged develop on the fork:
# git checkout feat/renovate-auto-patch-fork -- <same files>
```

No content changes should be needed — `RENOVATE_REPOSITORIES: ${{ github.repository }}` makes the workflow portable.

## Step 2.2 — Upstream-only manual setup (request from itk-dev admins)

Mirror the fork's manual setup, but on upstream:

1. **Repository secrets on `itk-dev/devops_itksites`:**
   - `RENOVATE_TOKEN` — a **GitHub App** installation token. Create a dedicated app (e.g. `renovate-itkdev`) on the itk-dev org with `contents:write`, `pull-requests:write`, `workflows:write` permissions; install it on `devops_itksites`; store the App ID + private key as `RENOVATE_APP_ID` / `RENOVATE_APP_PRIVATE_KEY` secrets; the workflow exchanges them for an installation token at runtime via `actions/create-github-app-token`. The App identity (e.g. `renovate-itkdev[bot]`) is what shows up in commits and audit logs — no personnel coupling.
   - `RELEASE_TOKEN` — same GitHub App token, or a separate App if you want auditing separation between Renovate pushes and release pushes. Needs `contents:write`, `workflows:write`.
2. **Branch protection on `main` and `develop`** — identical rule sets to the fork (status checks, up-to-date, auto-merge enabled, RELEASE_TOKEN allowed to push).
3. **CODEOWNERS** — if upstream has CODEOWNERS, either exempt `renovate[bot]` or accept that each PR needs a human approval before auto-merge.

## Step 2.3 — PR + handoff

```bash
$EDITOR CHANGELOG.md   # add bullet under [Unreleased]
git add -A
git commit -m "Add Renovate auto-patch + auto-release pipeline"
git push -u upstream feat/renovate-auto-patch
gh pr create --repo itk-dev/devops_itksites --base develop --fill
```

PR body should link to:

- This plan.
- The fork's successful Renovate run history (Actions → Renovate).
- The fork's first auto-release (`turegjorup/devops_itksites` Releases → `1.11.1`).

Reviewers can verify the diff is small and that all the behavior they're approving has already been observed running on the fork.

## Step 2.4 — Promote the files to upstream `main`

After the PR merges to `upstream/develop`, the same promotion question from Step 1.9 applies upstream. Recommend the same quick path: a small, scoped PR cherry-picking the four pipeline files from `develop` to `main`.

## Step 2.5 — First-run verification on upstream

Same as Step 1.10, but on upstream. Watch the first cycle closely — security PR first (0-day soak) so feedback comes quickly.

---

## Rollback

Applies to both fork and upstream:

- **Stop new PRs:** disable the `Renovate` workflow in the Actions tab.
- **Disable auto-release:** delete `.github/workflows/auto-release.yaml`. `github_build_release.yml` still works manually.
- **Disable auto-merge globally:** set top-level `automerge: false` in `renovate.json`. Renovate keeps opening PRs but waits for human merge.
- **Revert a bad back-merge:** the back-merge is `--no-ff`, so `git revert -m 1 <merge-sha>` cleanly undoes it on `develop` without touching the release on `main`.

## Resolved decisions

| Decision | Choice | Where it lives |
| --- | --- | --- |
| Upstream auth | **GitHub App** (`renovate-itkdev`-style). Personal PAT acceptable on the fork only. | Step 2.2 |
| Non-security soak window | **7 days.** Vuln-alert path overrides to 0 days for CVEs. | Step 1.2 patch package rule, Step 1.7 fork-side fallback |
| Version source for auto-release | **Git tags.** Highest `X.Y.Z` → bump patch. | Step 1.5 step 3 |
| Back-merge granularity | **Single grouped back-merge** per release, no per-package splitting. | Step 1.5 step 7 |

## Pre-flight checklist (do before Phase 1 first-run)

- [ ] Verify `CHANGELOG.md` on `main` does **not** already contain a `## [1.11.1]` header — if it does, either delete it or bump the auto-release seed to `1.11.2`. (Tag-based versioning will collide otherwise.)
- [ ] Confirm `composer.lock` is committed and matches `composer.json` (Renovate's `composer update` runs against the lock).
- [ ] Confirm `task lint` (or equivalent) passes locally on the four new files.
