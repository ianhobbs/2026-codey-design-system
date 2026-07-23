#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────
#  push-image.sh — scp a single file under build/ up to the server,
#  mirrored to the same relative path. Handy for a one-off asset without
#  a full rsync.
#
#    ./deploy/push-image.sh build/assets/images/hero.jpg
#
#  Connection comes from deploy/deploy.config.sh (same as deploy.sh).
# ─────────────────────────────────────────────────────────────────────

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
LOCAL_BUILD="$PROJECT_ROOT/build"

CONFIG="$SCRIPT_DIR/deploy.config.sh"
[[ -f "$CONFIG" ]] || { echo "✗ Missing $CONFIG — cp deploy/deploy.config.example.sh deploy/deploy.config.sh"; exit 1; }
# shellcheck source=/dev/null
source "$CONFIG"
: "${REMOTE:?}" "${PORT:?}" "${REMOTE_BUILD:?}"

LOCAL_FILE="${1:-}"
[[ -n "$LOCAL_FILE" && -f "$LOCAL_FILE" ]] || { echo "Usage: ./deploy/push-image.sh build/…/file  (file must exist under build/)"; exit 1; }

ABS_FILE="$(cd "$(dirname "$LOCAL_FILE")" && pwd)/$(basename "$LOCAL_FILE")"
case "$ABS_FILE" in
    "$LOCAL_BUILD"/*) REL="${ABS_FILE#"$LOCAL_BUILD"/}" ;;
    *) echo "✗ File must live under build/: $ABS_FILE" >&2; exit 1 ;;
esac

REMOTE_DIR="$REMOTE_BUILD/$(dirname "$REL")"
echo "→ $REL  to  $REMOTE:$REMOTE_DIR/"
scp -P "$PORT" "$ABS_FILE" "$REMOTE:$REMOTE_DIR/"
echo "✓ Uploaded $REL"
