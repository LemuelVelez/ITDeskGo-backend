#!/bin/sh
set -eu

ENV_FILE="${CI_ENV_FILE:-/var/www/html/.env}"
START_MARKER="# --- docker runtime config: start ---"
END_MARKER="# --- docker runtime config: end ---"
TMP_FILE="${ENV_FILE}.tmp"

escape_env_value() {
    printf "%s" "$1" | sed "s/'/'\\''/g"
}

write_env_line() {
    key="$1"
    value="$2"

    if [ -n "$value" ]; then
        printf "%s = '%s'\n" "$key" "$(escape_env_value "$value")" >> "$TMP_FILE"
    fi
}

first_env_value() {
    for key in "$@"; do
        eval "value=\${$key:-}"

        if [ -n "$value" ]; then
            printf "%s" "$value"
            return 0
        fi
    done

    return 0
}

mkdir -p "$(dirname "$ENV_FILE")"
touch "$ENV_FILE"

awk -v start="$START_MARKER" -v end="$END_MARKER" '
    $0 == start { skip = 1; next }
    $0 == end { skip = 0; next }
    skip != 1 { print }
' "$ENV_FILE" > "$TMP_FILE"

APP_BASE_URL_VALUE="$(first_env_value APP_BASE_URL)"
DB_DRIVER_VALUE="$(first_env_value DB_DRIVER DATABASE_DEFAULT_DBDRIVER)"
DB_HOST_VALUE="$(first_env_value DB_HOST DATABASE_DEFAULT_HOSTNAME)"
DB_NAME_VALUE="$(first_env_value DB_NAME DATABASE_DEFAULT_DATABASE)"
DB_USER_VALUE="$(first_env_value DB_USER DATABASE_DEFAULT_USERNAME)"
DB_PASS_VALUE="$(first_env_value DB_PASS DATABASE_DEFAULT_PASSWORD)"
DB_PORT_VALUE="$(first_env_value DB_PORT DATABASE_DEFAULT_PORT)"

printf "\n%s\n" "$START_MARKER" >> "$TMP_FILE"
write_env_line "CI_ENVIRONMENT" "${CI_ENVIRONMENT:-production}"
write_env_line "app.baseURL" "$APP_BASE_URL_VALUE"
write_env_line "database.default.DBDriver" "${DB_DRIVER_VALUE:-MySQLi}"
write_env_line "database.default.hostname" "$DB_HOST_VALUE"
write_env_line "database.default.database" "$DB_NAME_VALUE"
write_env_line "database.default.username" "$DB_USER_VALUE"
write_env_line "database.default.password" "$DB_PASS_VALUE"
write_env_line "database.default.port" "${DB_PORT_VALUE:-3306}"
printf "%s\n" "$END_MARKER" >> "$TMP_FILE"

mv "$TMP_FILE" "$ENV_FILE"
chown www-data:www-data "$ENV_FILE" 2>/dev/null || true
chmod 600 "$ENV_FILE" 2>/dev/null || true

exec "$@"
