#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════════════
#  deploy.sh — rsync deploy for a Codey/Kirby project (push / pull / seed)
#
#  Deployment model
#  ────────────────
#    • CODE ships via Git + Composer on the server:
#        - git pull   → src/, build/site, build/assets/{css,js}, bootstrap
#        - composer   → build/kirby, build/vendor, build/site/plugins
#    • rsync carries only what Git & Composer DON'T:
#        - PUSH  fonts + images (gitignored binaries the server serves)
#        - PUSH  glue/ (env / secrets, outside the web root)
#        - PULL  content + accounts + licenses (server is the source of truth)
#
#  Usage  (dry-run by default — add 'go' to transfer for real)
#  ─────
#    ./deploy/deploy.sh push [go]                 fonts + images  → server
#    ./deploy/deploy.sh glue [go]                 glue/ env files → server
#    ./deploy/deploy.sh pull [target] [go]        server → local (server-owned)
#        targets: content | accounts | config | glue | all   (default: all)
#    ./deploy/deploy.sh new-server [go]           seed content, accounts, license, glue
#
#  Connection settings live in deploy/deploy.config.sh (gitignored).
#  Copy deploy/deploy.config.example.sh to create it.
# ═══════════════════════════════════════════════════════════════════════

set -euo pipefail

# ── Paths (resolved relative to this script — no hardcoded absolutes) ──
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
LOCAL_BUILD="$PROJECT_ROOT/build"
LOCAL_GLUE="$PROJECT_ROOT/glue"

# ── Connection (from deploy.config.sh) ────────────────────────────────
CONFIG="$SCRIPT_DIR/deploy.config.sh"
if [[ ! -f "$CONFIG" ]]; then
    echo "✗ Missing $CONFIG"
    echo "  Create it:  cp deploy/deploy.config.example.sh deploy/deploy.config.sh"
    echo "  then fill in REMOTE / PORT / REMOTE_BUILD / REMOTE_GLUE."
    exit 1
fi
# shellcheck source=/dev/null
source "$CONFIG"
: "${REMOTE:?set REMOTE in deploy.config.sh}"
: "${PORT:?set PORT in deploy.config.sh}"
: "${REMOTE_BUILD:?set REMOTE_BUILD in deploy.config.sh}"
: "${REMOTE_GLUE:?set REMOTE_GLUE in deploy.config.sh}"

# glue/.env.local (local-only secrets) and *.bak must never sync either way.
GLUE_EXCLUDES=(--exclude=".env.local" --exclude="*.bak" --exclude=".env.example")

# ── Args: mode + optional target; 'go' anywhere = real transfer ───────
MODE="${1:-}"
DRY_RUN=true
for arg in "$@"; do [[ "$arg" == "go" ]] && DRY_RUN=false; done

# ── Helpers ───────────────────────────────────────────────────────────
run_rsync() {
    local label="$1" src="$2" dest="$3"; shift 3
    echo ""
    echo "── $label ──────────────────────────────────────────"
    local args=(rsync -azLKv --progress -e "ssh -p $PORT")
    [[ $# -gt 0 ]] && args+=("$@")
    $DRY_RUN && args+=(--dry-run)
    "${args[@]}" "$src" "$dest"
}

pull_dir() {
    local label="$1" remote_rel="$2" local_path="$3"; shift 3
    mkdir -p "$local_path"
    run_rsync "$label" "$REMOTE:$REMOTE_BUILD/$remote_rel/" "$local_path/" "$@"
}

push_dir() {
    local label="$1" local_rel="$2"; shift 2
    local src="$LOCAL_BUILD/$local_rel/"
    if [[ ! -d "$src" ]]; then echo "  ⚠  skip $label — not found: $src"; return; fi
    run_rsync "$label" "$src" "$REMOTE:$REMOTE_BUILD/$local_rel/" "$@"
}

banner() {
    echo "╔══════════════════════════════════════════════════════════╗"
    echo "║  $1"
    $DRY_RUN && echo "║  DRY RUN — nothing transferred (add 'go' to run for real)"
    echo "╚══════════════════════════════════════════════════════════╝"
}

# ── Dispatch ──────────────────────────────────────────────────────────
case "$MODE" in
    push)
        banner "PUSH assets (fonts + images) → $REMOTE:$REMOTE_BUILD"
        push_dir "fonts"  "assets/fonts"  --delete
        push_dir "images" "assets/images" --delete
        ;;

    glue)
        banner "PUSH glue (env / secrets) → $REMOTE:$REMOTE_GLUE"
        mkdir -p "$LOCAL_GLUE"
        run_rsync "glue" "$LOCAL_GLUE/" "$REMOTE:$REMOTE_GLUE/" --delete "${GLUE_EXCLUDES[@]}"
        ;;

    pull)
        TARGET="${2:-all}"; [[ "$TARGET" == "go" ]] && TARGET="all"
        banner "PULL ($TARGET) ← $REMOTE"
        case "$TARGET" in
            all)
                pull_dir "content"  "content"       "$LOCAL_BUILD/content/" --delete
                pull_dir "accounts" "site/accounts" "$LOCAL_BUILD/site/accounts/"
                mkdir -p "$LOCAL_GLUE"
                run_rsync "glue" "$REMOTE:$REMOTE_GLUE/" "$LOCAL_GLUE/" --delete "${GLUE_EXCLUDES[@]}"
                ;;
            content)  pull_dir "content"  "content"       "$LOCAL_BUILD/content/" --delete ;;
            accounts) pull_dir "accounts" "site/accounts" "$LOCAL_BUILD/site/accounts/" ;;
            config)   pull_dir "site/config (incl. .license*)" "site/config" "$LOCAL_BUILD/site/config/" ;;
            glue)
                mkdir -p "$LOCAL_GLUE"
                run_rsync "glue" "$REMOTE:$REMOTE_GLUE/" "$LOCAL_GLUE/" --delete "${GLUE_EXCLUDES[@]}"
                ;;
            *) echo "Unknown pull target: $TARGET (content|accounts|config|glue|all)"; exit 1 ;;
        esac
        ;;

    new-server)
        banner "NEW SERVER SEED — content, accounts, licenses, glue"
        # Only the non-git / non-composer files. Code comes from git pull + composer.
        push_dir "content"  "content"       --delete
        push_dir "accounts" "site/accounts"
        echo ""
        echo "── licenses ────────────────────────────────────────"
        shopt -s nullglob
        LICENSE_FILES=("$LOCAL_BUILD"/site/config/.license*)
        shopt -u nullglob
        if [[ ${#LICENSE_FILES[@]} -gt 0 ]]; then
            lic=(rsync -azLKv --progress -e "ssh -p $PORT"); $DRY_RUN && lic+=(--dry-run)
            "${lic[@]}" "${LICENSE_FILES[@]}" "$REMOTE:$REMOTE_BUILD/site/config/"
        else
            echo "  ⚠  No .license* in build/site/config/ — run './deploy/deploy.sh pull config go' first."
        fi
        mkdir -p "$LOCAL_GLUE"
        run_rsync "glue" "$LOCAL_GLUE/" "$REMOTE:$REMOTE_GLUE/" --delete "${GLUE_EXCLUDES[@]}"
        echo ""
        echo "  Reminder — on the server: git pull → composer install → restart php-fpm."
        ;;

    *)
        echo "Usage: ./deploy/deploy.sh <push|glue|pull|new-server> [target] [go]"
        echo "  push                 fonts + images → server"
        echo "  glue                 glue/ env files → server"
        echo "  pull [target]        server → local (content|accounts|config|glue|all)"
        echo "  new-server           seed content, accounts, licenses, glue"
        echo ""
        echo "  Dry-run by default. Add 'go' to transfer for real."
        exit 1
        ;;
esac

echo ""
echo "✓ Done."
