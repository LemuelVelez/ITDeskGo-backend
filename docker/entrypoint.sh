#!/bin/sh
set -eu

ENV_FILE="${CI_ENV_FILE:-/var/www/html/.env}"
START_MARKER="# --- docker runtime config: start ---"
END_MARKER="# --- docker runtime config: end ---"
TMP_FILE="${ENV_FILE}.tmp"

escape_env_value() {
    printf "%s" "$1" | sed "s/'/'\\\\''/g"
}

write_env_line() {
    key="$1"
    value="$2"

    if [ -n "$value" ]; then
        printf "%s = '%s'\n" "$key" "$(escape_env_value "$value")" >> "$TMP_FILE"
    fi
}

mkdir -p "$(dirname "$ENV_FILE")"
touch "$ENV_FILE"

awk -v start="$START_MARKER" -v end="$END_MARKER" '
    $0 == start { skip = 1; next }
    $0 == end { skip = 0; next }
    skip != 1 { print }
' "$ENV_FILE" > "$TMP_FILE"

printf "\n%s\n" "$START_MARKER" >> "$TMP_FILE"
write_env_line "CI_ENVIRONMENT" "${CI_ENVIRONMENT:-production}"
write_env_line "app.baseURL" "${APP_BASE_URL:-}"
write_env_line "database.default.DBDriver" "${DATABASE_DEFAULT_DBDRIVER:-MySQLi}"
write_env_line "database.default.hostname" "${DATABASE_DEFAULT_HOSTNAME:-}"
write_env_line "database.default.database" "${DATABASE_DEFAULT_DATABASE:-}"
write_env_line "database.default.username" "${DATABASE_DEFAULT_USERNAME:-}"
write_env_line "database.default.password" "${DATABASE_DEFAULT_PASSWORD:-}"
write_env_line "database.default.port" "${DATABASE_DEFAULT_PORT:-3306}"
printf "%s\n" "$END_MARKER" >> "$TMP_FILE"

mv "$TMP_FILE" "$ENV_FILE"
chown www-data:www-data "$ENV_FILE" 2>/dev/null || true
chmod 600 "$ENV_FILE" 2>/dev/null || true

exec "$@"
