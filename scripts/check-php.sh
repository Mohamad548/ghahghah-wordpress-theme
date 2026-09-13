#!/usr/bin/env bash
# Recursive PHP syntax check for theme and plugin sources.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FAIL=0
COUNT=0

while IFS= read -r -d '' file; do
	COUNT=$((COUNT + 1))
	if ! php -l "$file" > /dev/null; then
		echo "SYNTAX FAIL: $file"
		FAIL=1
	fi
done < <(find "$ROOT/ghahghah-theme" "$ROOT/ghahghah-core" -type f -name '*.php' -print0)

if [[ "$FAIL" -ne 0 ]]; then
	echo "PHP syntax check failed."
	exit 1
fi

echo "PHP syntax OK ($COUNT files)."
