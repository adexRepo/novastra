#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'

APP_ROOT="$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
SECRET_ENV="${DEPLOY_ENV_FILE:-${APP_ROOT}/secret/.env}"
LOG_DIRECTORY="${APP_ROOT}/storage/logs"
LOCK_DIRECTORY="${APP_ROOT}/storage/framework/.deploy-lock"
STARTED_AT="$(date +%s)"
STEP_NUMBER=0
CURRENT_STEP="initialization"
MAINTENANCE_ENABLED=0

mkdir -p "$LOG_DIRECTORY" "$(dirname -- "$LOCK_DIRECTORY")"
LOG_FILE="${LOG_DIRECTORY}/deploy-$(date '+%Y%m%d-%H%M%S').log"
exec > >(tee -a "$LOG_FILE") 2>&1

log() {
    printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"
}

fail() {
    log "ERROR: $*"
    exit 1
}

run_step() {
    STEP_NUMBER=$((STEP_NUMBER + 1))
    CURRENT_STEP="$1"
    shift

    log "STEP ${STEP_NUMBER} START - ${CURRENT_STEP}"
    "$@"
    log "STEP ${STEP_NUMBER} DONE  - ${CURRENT_STEP}"
}

find_executable() {
    local configured="$1"
    shift

    if [[ -n "$configured" && -x "$configured" ]]; then
        printf '%s\n' "$configured"
        return 0
    fi

    local candidate
    for candidate in "$@"; do
        if [[ -n "$candidate" && -x "$candidate" ]]; then
            printf '%s\n' "$candidate"
            return 0
        fi
    done

    return 1
}

prepare_node_path() {
    if [[ -n "${NODE_BIN_DIR:-}" && -x "${NODE_BIN_DIR}/node" && -x "${NODE_BIN_DIR}/npm" ]]; then
        PATH="${NODE_BIN_DIR}:${PATH}"
        export PATH
        return
    fi

    local directory
    for directory in /opt/cpanel/ea-nodejs24/bin /opt/cpanel/ea-nodejs22/bin /opt/cpanel/ea-nodejs20/bin; do
        if [[ -x "${directory}/node" && -x "${directory}/npm" ]]; then
            PATH="${directory}:${PATH}"
            export PATH
            return
        fi
    done
}

preflight() {
    [[ -f "${APP_ROOT}/artisan" ]] || fail "artisan tidak ditemukan di ${APP_ROOT}."
    [[ -f "${APP_ROOT}/composer.lock" ]] || fail "composer.lock tidak ditemukan."
    [[ -f "${APP_ROOT}/package-lock.json" ]] || fail "package-lock.json tidak ditemukan."
    [[ -f "$SECRET_ENV" ]] || fail "File environment tidak ditemukan: ${SECRET_ENV}. Buat secret/.env terlebih dahulu."

    PHP_EXECUTABLE="$(find_executable "${PHP_BIN:-}" /usr/local/bin/ea-php84 /opt/cpanel/ea-php84/root/usr/bin/php "$(command -v php 2>/dev/null || true)")" \
        || fail "PHP CLI 8.3+ tidak ditemukan. Set PHP_BIN ke path PHP Rumahweb."
    export PHP_EXECUTABLE

    COMPOSER_EXECUTABLE="$(find_executable "${COMPOSER_BIN:-}" "${HOME}/bin/composer" "$(command -v composer 2>/dev/null || true)")" \
        || fail "Composer tidak ditemukan. Set COMPOSER_BIN ke path Composer."
    export COMPOSER_EXECUTABLE

    prepare_node_path
    command -v node >/dev/null 2>&1 || fail "Node.js tidak ditemukan. Aktifkan Node.js 20.19+ atau 22.12+ di cPanel."
    command -v npm >/dev/null 2>&1 || fail "npm tidak ditemukan pada PATH cron."

    "$PHP_EXECUTABLE" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
        || fail "Versi PHP CLI harus minimal 8.3."
    node -e 'const [major, minor] = process.versions.node.split(".").map(Number); process.exit(major > 22 || major === 22 && minor >= 12 || major === 20 && minor >= 19 ? 0 : 1)' \
        || fail "Vite membutuhkan Node.js 20.19+, 22.12+, atau versi yang lebih baru."

    log "Repository : ${APP_ROOT}"
    log "Commit     : $(git -C "$APP_ROOT" log -1 --oneline 2>/dev/null || printf 'tidak tersedia')"
    log "PHP        : $("$PHP_EXECUTABLE" -r 'echo PHP_VERSION;')"
    log "Node.js    : $(node --version)"
    log "npm        : $(npm --version)"
    log "Log file   : ${LOG_FILE}"
}

install_environment() {
    chmod 700 "$(dirname -- "$SECRET_ENV")"
    cp "$SECRET_ENV" "${APP_ROOT}/.env"
    chmod 600 "${APP_ROOT}/.env"

    grep -Eq '^APP_ENV=(production|"production"|'"'"'production'"'"')[[:space:]]*$' "${APP_ROOT}/.env" \
        || fail "APP_ENV pada secret/.env harus bernilai production."
    grep -Eq '^APP_DEBUG=(false|0|"false"|'"'"'false'"'"')[[:space:]]*$' "${APP_ROOT}/.env" \
        || fail "APP_DEBUG pada secret/.env harus bernilai false."
    grep -Eq '^APP_KEY=.+$' "${APP_ROOT}/.env" \
        || fail "APP_KEY belum diisi pada secret/.env. Jangan membuat key baru saat redeploy."

    log "Environment production dipasang tanpa mencetak nilai rahasia."
}

enable_maintenance() {
    if [[ -f "${APP_ROOT}/vendor/autoload.php" ]]; then
        "$PHP_EXECUTABLE" artisan down --retry=60 --no-interaction
        MAINTENANCE_ENABLED=1
    else
        log "Vendor belum tersedia; maintenance mode dilewati untuk instalasi pertama."
    fi
}

install_php_dependencies() {
    "$PHP_EXECUTABLE" "$COMPOSER_EXECUTABLE" install \
        --no-dev \
        --prefer-dist \
        --optimize-autoloader \
        --no-interaction
}

build_frontend() {
    npm ci --no-audit --no-fund
    npm run build
    [[ -f "${APP_ROOT}/public/build/manifest.json" ]] || fail "Build selesai tanpa menghasilkan public/build/manifest.json."
}

prepare_directories() {
    mkdir -p \
        storage/app/private \
        storage/app/tmp \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        public/uploads/products

    chmod -R u+rwX storage bootstrap/cache public/uploads
}

clear_old_caches() {
    "$PHP_EXECUTABLE" artisan config:clear --no-interaction
    "$PHP_EXECUTABLE" artisan route:clear --no-interaction
    "$PHP_EXECUTABLE" artisan view:clear --no-interaction
    "$PHP_EXECUTABLE" artisan event:clear --no-interaction
}

run_migrations() {
    "$PHP_EXECUTABLE" artisan migrate --force --no-interaction
}

optimize_application() {
    "$PHP_EXECUTABLE" artisan optimize --no-interaction
}

verify_application() {
    "$PHP_EXECUTABLE" artisan migrate:status --no-interaction
    "$PHP_EXECUTABLE" artisan route:list --except-vendor --no-interaction >/dev/null
    log "Bootstrap aplikasi, database, dan route berhasil diverifikasi."
}

disable_maintenance() {
    if [[ "$MAINTENANCE_ENABLED" -eq 1 ]]; then
        "$PHP_EXECUTABLE" artisan up --no-interaction
        MAINTENANCE_ENABLED=0
    fi
}

cleanup() {
    local status=$?
    set +e

    if [[ "$MAINTENANCE_ENABLED" -eq 1 && -n "${PHP_EXECUTABLE:-}" && -f "${APP_ROOT}/vendor/autoload.php" ]]; then
        log "Memastikan aplikasi kembali online setelah kegagalan."
        "$PHP_EXECUTABLE" artisan up --no-interaction
    fi

    rm -f "${LOCK_DIRECTORY}/pid"
    rmdir "$LOCK_DIRECTORY" 2>/dev/null

    local duration=$(( $(date +%s) - STARTED_AT ))
    if [[ "$status" -eq 0 ]]; then
        log "DEPLOYMENT COMPLETED in ${duration}s"
    else
        log "DEPLOYMENT FAILED pada step '${CURRENT_STEP}' (exit ${status}, ${duration}s)"
    fi
    log "Detail log: ${LOG_FILE}"
}

acquire_lock() {
    if ! mkdir "$LOCK_DIRECTORY" 2>/dev/null; then
        local existing_pid=""
        if [[ -f "${LOCK_DIRECTORY}/pid" ]]; then
            existing_pid="$(<"${LOCK_DIRECTORY}/pid")"
        fi

        if [[ "$existing_pid" =~ ^[0-9]+$ ]] && kill -0 "$existing_pid" 2>/dev/null; then
            fail "Deployment lain masih berjalan dengan PID ${existing_pid}."
        fi

        rm -f "${LOCK_DIRECTORY}/pid"
        rmdir "$LOCK_DIRECTORY" 2>/dev/null || fail "Lock deployment lama tidak dapat dibersihkan: ${LOCK_DIRECTORY}."
        mkdir "$LOCK_DIRECTORY"
    fi

    printf '%s\n' "$$" > "${LOCK_DIRECTORY}/pid"
}

cd "$APP_ROOT"
acquire_lock
trap cleanup EXIT

log "NOVASTRA PRODUCTION DEPLOYMENT STARTED"
run_step "Preflight server dan repository" preflight
run_step "Pasang environment dari secret/.env" install_environment
run_step "Aktifkan maintenance mode" enable_maintenance
run_step "Install dependency PHP production" install_php_dependencies
run_step "Build aset frontend" build_frontend
run_step "Siapkan direktori dan permission" prepare_directories
run_step "Bersihkan cache deployment lama" clear_old_caches
run_step "Jalankan migration database" run_migrations
run_step "Optimasi Laravel" optimize_application
run_step "Verifikasi aplikasi" verify_application
run_step "Aktifkan kembali aplikasi" disable_maintenance
