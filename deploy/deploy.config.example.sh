#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────
#  deploy.config.example.sh — connection settings for deploy.sh
#
#  COPY this file to  deploy/deploy.config.sh  and fill in your values.
#  deploy.config.sh is gitignored (it holds your server address), so each
#  project/clone sets its own. This example is committed as the template.
#
#      cp deploy/deploy.config.example.sh deploy/deploy.config.sh
#      # then edit deploy/deploy.config.sh
# ─────────────────────────────────────────────────────────────────────

# SSH target — user@host (or user@IP)
REMOTE="user@SERVER_IP"

# SSH port (22 unless your host uses a custom one)
PORT="22"

# Remote paths on the server.
#   REMOTE_BUILD — the project's web root, i.e. the deployed build/ folder.
#   REMOTE_GLUE  — env / secrets folder, kept OUTSIDE the web root.
REMOTE_BUILD="example.com/build"
REMOTE_GLUE="example.com/glue"
