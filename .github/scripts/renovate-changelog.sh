#!/usr/bin/env bash
# Called by Renovate postUpgradeTasks. Appends a single bullet under the
# `## [Unreleased]` section of CHANGELOG.md so the auto-release workflow has
# content to promote into a tagged release after the Renovate PR merges to main.
set -euo pipefail

BRANCH="${1:-renovate/unknown}"
TITLE="${2:-Update dependencies}"

# Skip if CHANGELOG.md was already touched this run (grouped PRs fire the hook
# more than once; we only want one bullet per PR).
if git diff --cached --name-only | grep -qx CHANGELOG.md; then
  exit 0
fi
if git diff --name-only | grep -qx CHANGELOG.md; then
  exit 0
fi

ENTRY="- Renovate: ${TITLE} (\`${BRANCH}\`)"

python3 - "$ENTRY" <<'PY'
import re, sys, pathlib
entry = sys.argv[1]
p = pathlib.Path("CHANGELOG.md")
text = p.read_text(encoding="utf-8")

pattern = re.compile(r"(## \[Unreleased\]\n)(.*?)(\n## \[)", re.DOTALL)
m = pattern.search(text)
if not m:
    # No [Unreleased] section yet — insert one after the intro paragraph
    # (the first blank line after the H1 header).
    head, _, rest = text.partition("\n\n")
    text = f"{head}\n\n## [Unreleased]\n\n{entry}\n\n{rest}"
else:
    body = m.group(2).rstrip("\n")
    sep  = "\n" if body else ""
    text = pattern.sub(
        lambda _m: f"{_m.group(1)}{body}{sep}{entry}\n{_m.group(3)}",
        text, count=1,
    )

p.write_text(text, encoding="utf-8")
PY
