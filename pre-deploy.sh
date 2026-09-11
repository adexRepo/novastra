#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'

APP_ROOT="$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"

log() {
    printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"
}

fail() {
    log "ERROR: $*" >&2
    exit 1
}

cd "$APP_ROOT"

[[ -f package-lock.json ]] || fail "package-lock.json tidak ditemukan."
command -v node >/dev/null 2>&1 || fail "Node.js tidak ditemukan di laptop."
command -v npm >/dev/null 2>&1 || fail "npm tidak ditemukan di laptop."

node -e 'const [major, minor] = process.versions.node.split(".").map(Number); process.exit(major > 22 || major === 22 && minor >= 12 || major === 20 && minor >= 19 ? 0 : 1)' \
    || fail "Vite membutuhkan Node.js 20.19+, 22.12+, atau versi yang lebih baru."

log "PRE-DEPLOY STARTED"
log "Node.js: $(node --version)"
log "npm    : $(npm --version)"

log "Install dependency frontend yang terkunci"
npm ci --no-audit --no-fund

log "Jalankan test JavaScript"
npm run test:js

log "Build aset frontend production"
npm run build

[[ -s public/build/manifest.json ]] || fail "Build tidak menghasilkan public/build/manifest.json."

node <<'NODE'
const fs = require('node:fs');
const path = require('node:path');

const buildRoot = path.resolve('public/build');
const manifestPath = path.join(buildRoot, 'manifest.json');
const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));

if (Object.keys(manifest).length === 0) {
    throw new Error('Manifest frontend kosong.');
}

for (const entry of Object.values(manifest)) {
    const files = [entry.file, ...(entry.css ?? []), ...(entry.assets ?? [])].filter(Boolean);

    for (const file of files) {
        const absolutePath = path.resolve(buildRoot, file);

        if (!absolutePath.startsWith(`${buildRoot}${path.sep}`) || !fs.statSync(absolutePath).isFile()) {
            throw new Error(`Aset pada manifest tidak valid atau tidak ditemukan: ${file}`);
        }
    }
}
NODE

log "PRE-DEPLOY COMPLETED"
log "Commit seluruh perubahan, termasuk public/build, lalu push ke repository."
