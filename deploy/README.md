# deploy/

rsync deploy for a Codey/Kirby project. Git + Composer ship the code; rsync
carries only what they don't.

## What goes how

| Layer | Delivered by |
|-------|--------------|
| `src/`, `build/site`, `build/assets/{css,js}`, bootstrap | **git pull** on the server |
| `build/kirby`, `build/vendor`, `build/site/plugins` | **composer install** on the server |
| fonts + images (`build/assets/{fonts,images}`) | **rsync push** (gitignored binaries) |
| env / secrets (`glue/`) | **rsync push** (outside the web root) |
| content, accounts, licenses | **rsync pull** — server is the source of truth |

## Setup (once per project)

```bash
cp deploy/deploy.config.example.sh deploy/deploy.config.sh
# edit deploy/deploy.config.sh → REMOTE, PORT, REMOTE_BUILD, REMOTE_GLUE
```

`deploy.config.sh` is gitignored (it holds your server address); every clone sets
its own.

## Commands

Dry-run by default — add `go` to transfer for real.

```bash
./deploy/deploy.sh push [go]            # fonts + images → server
./deploy/deploy.sh glue [go]            # glue/ env files → server
./deploy/deploy.sh pull [target] [go]   # server → local  (content|accounts|config|glue|all)
./deploy/deploy.sh new-server [go]      # seed content, accounts, licenses, glue
./deploy/push-image.sh build/…/file     # scp one file under build/
```

npm aliases: `npm run deploy:push`, `deploy:pull`, `deploy:glue` (append `-- go` to run).

## First deploy to a new server

1. Point Git + Composer on the server at the repo (`git pull` → `composer install`).
2. `./deploy/deploy.sh new-server go` — seeds content, accounts, licenses, glue.
3. `./deploy/deploy.sh push go` — sends fonts + images.
4. On the server: restart php-fpm so env changes load.

Thereafter: `pull content` before editing locally; `push` after adding assets.
