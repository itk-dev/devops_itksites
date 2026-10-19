#!/bin/sh
# Runs a check in the phpfpm container. On failure the output goes to stderr
# with exit 2, which Claude Code feeds back to Claude. Skips when phpfpm is down.
#
# Usage: phpfpm-check.sh vendor/bin/phpstan analyse src/Kernel.php

cd "${CLAUDE_PROJECT_DIR:-.}" || exit 0
docker compose ps --status running --quiet phpfpm 2>/dev/null | grep -q . || exit 0

if ! output=$(docker compose exec -T phpfpm "$@" 2>&1); then
    printf '%s\n' "$output" >&2
    exit 2
fi
