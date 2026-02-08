#!/usr/bin/env bash

LOG_FILE="/var/www/html/storage/logs/entrypoint.log"

# Log messages to console and file
log() {
	TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
	MSG="[ENTRYPOINT]: $*"

	echo "${TIMESTAMP} ${MSG}" | tee -a "${LOG_FILE}"
}

# Create standard laravel log directory
prepare_log_file() {
	mkdir -p /var/www/html/storage/logs

	if [[ ! -w "/var/www/html/storage/logs" ]]; then
		echo "ERROR: Missing write permissions in storage/logs"
		exit 1
	fi

	# Create or truncate file
	: >"${LOG_FILE}"
}

# Install composer dependencies (Only in development)
install_dependencies() {
	if [[ ${APP_ENV} == "development" ]] && [[ ! -d "vendor" ]]; then
		log "Installing Composer dependencies..."
		composer install --no-interaction --prefer-dist
	fi
}

# Create .env file on each start to apply environment variable changes
generate_env_file() {
	local example=".env.example"
	local target=".env"

	if [[ ! -f ${example} ]]; then
		log "ERROR: .env.example not found"
		return 1
	fi

	if envsubst <"${example}" >"${target}"; then
		log "Created .env file from .env.example"
	else
		log "Error creating .env file from .env.example"
		exit 1
	fi
}

# Generate Laravel APP KEY if it's missing or empty
generate_app_key() {
	# Check if APP_KEY is already correctly set in .env
	if grep -q "^APP_KEY=base64:.\+" .env && ! grep -q "APP_KEY=${APP_KEY}" .env; then
		log "APP_KEY detected in .env file."
		return
	fi

	# If not in file, try to inject it from the environment variable provided by Docker
	# shellcheck disable=SC2016
	if [[ -n ${APP_KEY} ]] && [[ ${APP_KEY} != '${APP_KEY}' ]]; then
		log "Injecting APP_KEY from environment variable..."
		# We use | as delimiter for sed just in case the key contains /
		sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
		return
	fi

	# Security check for production
	if [[ ${APP_ENV} == "production" ]] || [[ ${APP_ENV} == "staging" ]]; then
		log "ERROR: No APP_KEY found in environment or .env file. Production requires a persistent key."
		exit 1
	fi

	# Development fallback: generate a new key if nothing else worked
	log "APP_KEY not found, generating a new key via Artisan..."
	php artisan key:generate --force
}

# Wait for Postgres to be running before executing migrations and seeding
wait_for_db() {
	log "Waiting for Postgres..."

	RETRIES=30
	PDO_CODE=$(
		set -e
		get_pdo
	)

	until [[ ${exit_code} -eq 0 ]] || [[ ${RETRIES} -le 0 ]]; do
		php -r "${PDO_CODE} get_connection();" >/dev/null 2>&1
		exit_code=$?

		if [[ ${exit_code} -ne 0 ]]; then
			RETRIES=$((RETRIES - 1))
			sleep 1
		fi
	done

	if [[ ${RETRIES} -le 0 ]] && [[ ${exit_code} -ne 0 ]]; then
		log "Error: Postgres was not reachable."
		# Attempt one last time without silencing to see the actual error in the console
		php -r "${PDO_CODE} get_connection();"
		exit 1
	fi
}

# Handle database migrations and initial seeding
run_migrations() {
	log "Checking database state using raw PHP PDO..."

	local DB_INFO
	local EXIT_CODE
	local TMP
	local PDO_CODE

	PDO_CODE=$(
		set -e
		get_pdo
	)
	TMP=$(mktemp)

	set +e
	php -r "
        ${PDO_CODE}
        try {
            \$pdo = get_connection();
            // Postgres: check if table exists in public schema
            \$stmt = \$pdo->query(\"SELECT to_regclass('public.migrations')\");
            \$val = \$stmt->fetchColumn();
            if (!empty(\$val)) {
                echo 'EXISTS';
            } else {
                echo 'EMPTY';
            }
        } catch (Exception \$e) {
            fwrite(STDERR, 'ERROR: ' . \$e->getMessage());
            exit(1);
        }
    " >"${TMP}" 2>&1
	EXIT_CODE=$?
	set -e

	DB_INFO=$(<"${TMP}")
	rm -f "${TMP}"

	if [[ ${EXIT_CODE} -ne 0 ]]; then
		log "DATABASE CONNECTION ERROR: ${DB_INFO}"
		exit 1
	fi

	if [[ ${DB_INFO} == "EXISTS" ]]; then
		log "Running migrations..."
		php artisan migrate --force
	else
		log "Running first-time setup: migrations and seeding..."
		php artisan migrate:fresh --force --seed
	fi
}

# Generates a reusable PHP PDO connection snippet for DB health and migration checks.
get_pdo() {
	cat <<'EOF'
        function get_connection() {
            $host = getenv('DB_HOST');
            $port = getenv('DB_PORT') ?: '5432';
            $db   = getenv('DB_DATABASE');
            $user = getenv('DB_USERNAME');
            $pass = getenv('DB_PASSWORD');
            $sslmode = getenv('DB_SSLMODE');

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ];

            try {
                // Supabase/Postgres: SSL required
                return new PDO("pgsql:host=$host;port=$port;dbname=$db;sslmode=$sslmode", $user, $pass, $options);
            } catch (Exception $e) {
                fwrite(STDERR, "CONN_ERROR: " . $e->getMessage());
                exit(1);
            }
        }
EOF
}

# Clear all cache files to avoid conflicts
clear_cache() {
	log "Clearing application cache..."
	rm -rf bootstrap/cache/*.php
	rm -rf storage/framework/views/*.php
	php artisan config:clear
	php artisan route:clear
	php artisan view:clear
}

# Clear and optimize cache
optimize_cache() {
	log "Optimizing application cache..."
	rm -rf bootstrap/cache/*.php
	rm -rf storage/framework/views/*.php
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
}

# --- MAIN EXECUTION ---

# Change to the application directory
cd /var/www/html || exit 1
prepare_log_file
install_dependencies
generate_env_file
generate_app_key
wait_for_db

if [[ ${APP_ENV} == "production" ]] || [[ ${APP_ENV} == "staging" ]]; then
	# Production: Migrate first, then Optimize
	run_migrations
	optimize_cache
else
	# Development: Clear caches first, then Migrate
	clear_cache
	run_migrations
fi

if [[ -z $1 ]]; then
	log "ERROR: No command (CMD) provided. Exiting to avoid loop."
	exit 1
fi

# Start the main process (PHP-FPM, Supervisor, etc.)
log "Starting process: $*"
exec "$@"
