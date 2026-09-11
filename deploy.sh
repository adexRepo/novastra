#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'

APP_ROOT="$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
ACCOUNT_ROOT="$(CDPATH= cd -- "${APP_ROOT}/../.." && pwd)"
SECRET_ENV="${DEPLOY_ENV_FILE:-${ACCOUNT_ROOT}/secret/.env}"
PUBLIC_ROOT="${DEPLOY_PUBLIC_ROOT:-${ACCOUNT_ROOT}/public_html}"
LOG_DIRECTORY="${DEPLOY_LOG_DIR:-${ACCOUNT_ROOT}/logs}"
LOCK_DIRECTORY="${APP_ROOT}/storage/framework/.deploy-lock"
STARTED_AT="$(date +%s)"
DEPLOY_ID="$(date '+%Y%m%d-%H%M%S')"
STEP_NUMBER=0
CURRENT_STEP="initialization"
MAINTENANCE_ENABLED=0

mkdir -p "$LOG_DIRECTORY" "$(dirname -- "$LOCK_DIRECTORY")"
chmod 700 "$LOG_DIRECTORY"
LOG_FILE="${LOG_DIRECTORY}/deploy-${DEPLOY_ID}.log"
STATUS_FILE="${LOG_DIRECTORY}/latest-status.txt"
touch "$LOG_FILE"
chmod 600 "$LOG_FILE"
printf '[%s] LOGGER INITIALIZED | pid=%s | repository=%s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$$" "$APP_ROOT" >> "$LOG_FILE"
printf 'RUNNING | %s | %s | pid=%s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$LOG_FILE" "$$" > "$STATUS_FILE"
chmod 600 "$STATUS_FILE"
exec >> "$LOG_FILE" 2>&1

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

require_env_path() {
    local key="$1"
    local expected="$2"
    local configured

    configured="$(sed -n "s/^${key}=//p" "${APP_ROOT}/.env" | tail -n 1 | tr -d '\r')"
    configured="${configured#\"}"
    configured="${configured%\"}"
    configured="${configured#\'}"
    configured="${configured%\'}"

    [[ "$configured" = "$expected" ]] || fail "${key} harus bernilai ${expected}."
}

preflight() {
    [[ -f "${APP_ROOT}/artisan" ]] || fail "artisan tidak ditemukan di ${APP_ROOT}."
    [[ -f "${APP_ROOT}/composer.lock" ]] || fail "composer.lock tidak ditemukan."
    [[ -s "${APP_ROOT}/public/build/manifest.json" ]] || fail "public/build/manifest.json tidak ditemukan. Jalankan pre-deploy.sh di laptop, commit public/build, lalu pull ulang di cPanel."
    [[ -f "$SECRET_ENV" ]] || fail "File environment tidak ditemukan: ${SECRET_ENV}. Buat secret/.env terlebih dahulu."
    command -v realpath >/dev/null 2>&1 || fail "realpath tidak ditemukan pada server."
    PUBLIC_ROOT="$(realpath -m -- "$PUBLIC_ROOT")"
    [[ "$PUBLIC_ROOT" = "${ACCOUNT_ROOT}/"* ]] || fail "Public root harus berada di dalam ${ACCOUNT_ROOT}."
    [[ "$PUBLIC_ROOT" != "$ACCOUNT_ROOT" && "$PUBLIC_ROOT" != "/" ]] || fail "Public root tidak aman: ${PUBLIC_ROOT}."
    [[ "$PUBLIC_ROOT" != "${APP_ROOT}/public" ]] || fail "Public root tidak boleh sama dengan folder public repository."

    PHP_EXECUTABLE="$(find_executable "${PHP_BIN:-}" /usr/local/bin/ea-php85 /opt/cpanel/ea-php85/root/usr/bin/php /usr/local/bin/ea-php84 /opt/cpanel/ea-php84/root/usr/bin/php "$(command -v php 2>/dev/null || true)")" \
        || fail "PHP CLI 8.3+ tidak ditemukan. Set PHP_BIN ke path PHP Rumahweb."
    export PHP_EXECUTABLE

    COMPOSER_EXECUTABLE="$(find_executable "${COMPOSER_BIN:-}" "${HOME}/bin/composer" "$(command -v composer 2>/dev/null || true)")" \
        || fail "Composer tidak ditemukan. Set COMPOSER_BIN ke path Composer."
    export COMPOSER_EXECUTABLE

    command -v rsync >/dev/null 2>&1 || fail "rsync tidak ditemukan pada server."

    "$PHP_EXECUTABLE" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
        || fail "Versi PHP CLI harus minimal 8.3."
    log "Repository : ${APP_ROOT}"
    log "Environment: ${SECRET_ENV}"
    log "Public root: ${PUBLIC_ROOT}"
    log "Commit     : $(git -C "$APP_ROOT" log -1 --oneline 2>/dev/null || printf 'tidak tersedia')"
    log "PHP        : $("$PHP_EXECUTABLE" -r 'echo PHP_VERSION;')"
    log "Frontend   : prebuilt ($(wc -c < "${APP_ROOT}/public/build/manifest.json" | tr -d '[:space:]') byte manifest)"
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

    require_env_path "PUBLIC_UPLOAD_DIR" "${PUBLIC_ROOT}/uploads"
    require_env_path "PRIVATE_UPLOAD_DIR" "${APP_ROOT}/storage/app/private"
    require_env_path "TEMP_UPLOAD_DIR" "${APP_ROOT}/storage/app/tmp"

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

verify_frontend_build() {
    "$PHP_EXECUTABLE" -r '
        $buildRoot = realpath($argv[1]);
        $manifestPath = $argv[1].DIRECTORY_SEPARATOR."manifest.json";
        $manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

        if ($buildRoot === false || $manifest === []) {
            fwrite(STDERR, "Manifest frontend kosong atau tidak valid.\n");
            exit(1);
        }

        foreach ($manifest as $entry) {
            $files = array_filter(array_merge([$entry["file"] ?? null], $entry["css"] ?? [], $entry["assets"] ?? []));

            foreach ($files as $file) {
                $resolved = realpath($buildRoot.DIRECTORY_SEPARATOR.$file);

                if ($resolved === false || !str_starts_with($resolved, $buildRoot.DIRECTORY_SEPARATOR) || !is_file($resolved)) {
                    fwrite(STDERR, "Aset frontend tidak ditemukan atau tidak aman: {$file}\n");
                    exit(1);
                }
            }
        }
    ' "${APP_ROOT}/public/build"
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
        public/uploads/products \
        "${PUBLIC_ROOT}/build" \
        "${PUBLIC_ROOT}/uploads/products"

    chmod -R u+rwX storage bootstrap/cache public/uploads "${PUBLIC_ROOT}/uploads"
    chmod 700 storage/app/private storage/app/tmp
}

publish_public_files() {
    rsync -a --delete "${APP_ROOT}/public/build/" "${PUBLIC_ROOT}/build/"
    rsync -a \
        --exclude='/build/' \
        --exclude='/uploads/' \
        --exclude='/.htaccess' \
        "${APP_ROOT}/public/" \
        "${PUBLIC_ROOT}/"

    if [[ ! -f "${PUBLIC_ROOT}/.htaccess" ]]; then
        cp "${APP_ROOT}/public/.htaccess" "${PUBLIC_ROOT}/.htaccess"
    fi

    if [[ -f "${APP_ROOT}/public/uploads/products/novastra-fresh-collection.webp" && ! -f "${PUBLIC_ROOT}/uploads/products/novastra-fresh-collection.webp" ]]; then
        cp "${APP_ROOT}/public/uploads/products/novastra-fresh-collection.webp" "${PUBLIC_ROOT}/uploads/products/novastra-fresh-collection.webp"
    fi

    [[ -f "${PUBLIC_ROOT}/index.php" ]] || fail "public_html/index.php tidak berhasil dipasang."
    [[ -f "${PUBLIC_ROOT}/.htaccess" ]] || fail "public_html/.htaccess tidak berhasil dipasang."
    [[ -f "${PUBLIC_ROOT}/build/manifest.json" ]] || fail "Manifest frontend tidak ditemukan di public_html/build."
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

    if [[ "$status" -ne 0 && "$MAINTENANCE_ENABLED" -eq 1 ]]; then
        log "Aplikasi tetap dalam maintenance mode karena deployment gagal. Perbaiki error lalu jalankan deployment kembali."
    fi

    rm -f "${LOCK_DIRECTORY}/pid"
    rmdir "$LOCK_DIRECTORY" 2>/dev/null

    local duration=$(( $(date +%s) - STARTED_AT ))
    if [[ "$status" -eq 0 ]]; then
        log "DEPLOYMENT COMPLETED in ${duration}s"
        printf 'SUCCESS | %s | %s | %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$LOG_FILE" "$(git -C "$APP_ROOT" rev-parse --short HEAD 2>/dev/null || printf 'unknown')" > "$STATUS_FILE"
    else
        log "DEPLOYMENT FAILED pada step '${CURRENT_STEP}' (exit ${status}, ${duration}s)"
        printf 'FAILED | %s | %s | step=%s | exit=%s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$LOG_FILE" "$CURRENT_STEP" "$status" > "$STATUS_FILE"
    fi
    chmod 600 "$STATUS_FILE"
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
run_step "Verifikasi aset frontend prebuilt" verify_frontend_build
run_step "Siapkan direktori dan permission" prepare_directories
run_step "Publikasikan file web ke public_html" publish_public_files
run_step "Bersihkan cache deployment lama" clear_old_caches
run_step "Jalankan migration database" run_migrations
run_step "Optimasi Laravel" optimize_application
run_step "Verifikasi aplikasi" verify_application
run_step "Aktifkan kembali aplikasi" disable_maintenance
