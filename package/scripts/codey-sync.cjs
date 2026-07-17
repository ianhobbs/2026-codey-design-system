#!/usr/bin/env node
/**
 * codey-sync — copies the Codey design-system source from the installed
 * package into a consuming project's src/ tree, scoped STRICTLY to the
 * declared overwrite zones (see codey-sync.json).
 *
 * Triggered by Composer (post-install-cmd / post-update-cmd) and/or npm
 * (postinstall). Build-agnostic: only plain files land in src/, so CodeKit,
 * Vite, or a pure npm/Tailwind pipeline all compile them identically.
 *
 * Clobber-safety contract: the script wipes and re-copies ONLY the exact dest
 * zones declared in the manifest. Project-owned files (main.css, brand.css,
 * templates/*, site snippets) live outside those zones and are never touched.
 *
 * Usage:
 *   node codey-sync.cjs [--from <packageDir>] [--to <projectRoot>] [--dry]
 * Defaults:
 *   --from  the package dir containing codey-sync.json (…/vendor/ianhobbsmedia/codey-design-system)
 *   --to    process.cwd() — the consuming project root
 */
"use strict";
const fs = require("fs");
const path = require("path");

function parseArgs(argv) {
  const a = { dry: false };
  for (let i = 0; i < argv.length; i++) {
    if (argv[i] === "--from") a.from = argv[++i];
    else if (argv[i] === "--to") a.to = argv[++i];
    else if (argv[i] === "--dry") a.dry = true;
  }
  return a;
}

function countFiles(dir) {
  let n = 0;
  for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
    if (e.isDirectory()) n += countFiles(path.join(dir, e.name));
    else n++;
  }
  return n;
}

function copyDir(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  for (const e of fs.readdirSync(src, { withFileTypes: true })) {
    const s = path.join(src, e.name);
    const d = path.join(dest, e.name);
    if (e.isDirectory()) copyDir(s, d);
    else fs.copyFileSync(s, d);
  }
}

function assertInside(base, target) {
  const rel = path.relative(base, target);
  if (rel === "" || rel.startsWith("..") || path.isAbsolute(rel)) {
    throw new Error(`codey-sync: refusing to write outside the project: ${target}`);
  }
}

function main() {
  const args = parseArgs(process.argv.slice(2));
  const packageDir = path.resolve(args.from || path.join(__dirname, ".."));
  const projectDir = path.resolve(args.to || process.cwd());
  const manifestPath = path.join(packageDir, "codey-sync.json");

  if (!fs.existsSync(manifestPath)) {
    console.warn(`codey-sync: no manifest at ${manifestPath} — package not installed yet, skipping.`);
    process.exit(0);
  }

  const manifest = JSON.parse(fs.readFileSync(manifestPath, "utf8"));
  const version = manifest.version || "unknown";
  let total = 0;

  console.log(`codey-sync v${version}  (${args.dry ? "DRY RUN" : "writing"})`);
  console.log(`  from: ${packageDir}`);
  console.log(`  to:   ${projectDir}\n`);

  for (const zone of manifest.zones) {
    const from = path.join(packageDir, zone.from);
    const to = path.join(projectDir, zone.to);
    assertInside(projectDir, to); // structural clobber-safety

    if (!fs.existsSync(from)) {
      console.warn(`  skip (missing source): ${zone.from}`);
      continue;
    }
    if (args.dry) {
      console.log(`  [dry] ${zone.from}  ->  ${zone.to}`);
      continue;
    }
    fs.rmSync(to, { recursive: true, force: true }); // wipe ONLY this zone
    copyDir(from, to);
    const n = countFiles(to);
    total += n;
    console.log(`  synced ${zone.from}  ->  ${zone.to}  (${n} files)`);
  }

  if (!args.dry) {
    const stampDir = path.join(projectDir, "src");
    fs.mkdirSync(stampDir, { recursive: true });
    fs.writeFileSync(path.join(stampDir, ".codey-version"), version + "\n");
    console.log(`\ncodey-sync complete — v${version}, ${total} files into overwrite zones.`);
  }
}

main();
