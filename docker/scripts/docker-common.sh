#!/usr/bin/env bash

# Parse all arguments passed to the build script
parse_arguments() {
	# Set default values if not already set
	CLEAN_MODE=false
	ONLY_CLEAN=false

	while [[ $# -gt 0 ]]; do
		case $1 in
		--env=*) ENVIRONMENT="${1#*=}" ;;
		--clean) CLEAN_MODE=true ;;
		--only-clean)
			CLEAN_MODE=true
			ONLY_CLEAN=true
			;;
		*)
			echo "❌ Unknown parameter: $1"
			exit 1
			;;
		esac
		shift
	done

	if [[ ${ENVIRONMENT} != "development" && ${ENVIRONMENT} != "staging" && ${ENVIRONMENT} != "production" ]]; then
		echo "❌ Error: Wrong environment '${ENVIRONMENT}'. Valid values are 'development' or 'production'."
		exit 1
	fi

	# Export variables so they are available globally
	export ENVIRONMENT CLEAN_MODE ONLY_CLEAN
}

# Merge docker-compose.base.yaml and docker-compose.${ENVIRONMENT}.yaml into docker-compose.yaml
merge_docker_compose_files() {
	if [[ ${ENVIRONMENT} == "staging" || ${ENVIRONMENT} == "production" ]]; then
		docker compose -f docker-compose.base.yaml -f docker-compose.production.yaml config --no-interpolate >docker-compose.yaml
	else
		docker compose -f docker-compose.base.yaml -f docker-compose."${ENVIRONMENT}".yaml config --no-interpolate >docker-compose.yaml
	fi
}

# Create necessary .env files for docker
generate_env_files() {
	copy_env_example
	generate_app_key
	generate_commit_sha
	generate_docker_env
}

copy_env_example() {
	local env_file="${ROOT_DIR}/docker/envs/.env.${ENVIRONMENT}"
	local env_example_file="${ROOT_DIR}/docker/envs/.env.example"

	if [[ ! -f ${env_file} ]]; then
		cp "${env_example_file}" "${env_file}"
		printf "✅ Environment file %s created from .env.example\n" ".env.${ENVIRONMENT}"
	fi
}

# Get the last commit to be used in the docker image name
generate_commit_sha() {
	COMMIT_SHA=$(git rev-parse --short HEAD)
	export COMMIT_SHA
}

# Create the .env.${ENVIRONMENT}.secrets file with APP_KEY
generate_app_key() {
	local env_file="${ROOT_DIR}/docker/envs/.env.${ENVIRONMENT}"
	local key

	# Check if APP_KEY exists and is not empty
	if ! grep -q "^APP_KEY=base64:" "${env_file}"; then
		if command -v openssl >/dev/null 2>&1; then
			key=$(openssl rand -base64 32)

			# If the line exists but is empty or invalid, replace it. Otherwise, append.
			if grep -q "^APP_KEY=" "${env_file}"; then
				sed -i "s|^APP_KEY=.*|APP_KEY=base64:${key}|" "${env_file}"
				printf "✅ App key appended to the %s file\n" ".env.${ENVIRONMENT}"
			else
				local ends_with_newline
				ends_with_newline=$(tail -c1 "${env_file}" | wc -l || true)

				# Ensure the APP_KEY is appended on a new line
				if [[ -s ${env_file} ]] && [[ ${ends_with_newline} -eq 0 ]]; then
					echo "" >>"${env_file}"
				fi

				echo "APP_KEY=base64:${key}" >>"${env_file}"
				printf "✅ App key added to the %s file\n" ".env.${ENVIRONMENT}"
			fi
		else
			printf "\r\033[K⚠️ OpenSSL not found. Please set APP_KEY manually in .env\n"
		fi
	fi
}

# Merge env files into a single file and export variable
generate_docker_env() {
	local date
	local server_uid
	local server_gid
	local env_file="docker/envs/.env.${ENVIRONMENT}"
	local target_env_file="${ROOT_DIR}/.env"
	local tmp_env="${ROOT_DIR}/.env.tmp"

	date=$(date '+%Y-%m-%d %H:%M:%S')
	server_uid=$(id -u)
	server_gid=$(id -g)

	# Merge .env files
	{
		printf "# ==========================================================\n"
		printf "# GENERATED ENVIRONMENT FILE for %s\n" "${ENVIRONMENT}"
		printf "# Generated at: %s\n" "${date}"
		printf "# ==========================================================\n\n"

		printf "# --- BASE CONFIGURATION ---\n\n"

		echo "COMPOSE_PROJECT_NAME=laa-${ENVIRONMENT}"
		echo "SERVER_UID=${server_uid}"
		echo "SERVER_GID=${server_gid}"
		echo "COMMIT_SHA=${COMMIT_SHA}"
		echo ""

		if [[ -n ${env_file} ]] && [[ -f ${env_file} ]]; then
			cat "${env_file}"
		fi

	} >"${tmp_env}"

	# Create an isolated environment to export variables for envsubst.
	# Once the subshell finishes, these variables are cleared from memory.
	(
		set -a
		source "${tmp_env}"
		envsubst <"${tmp_env}" >"${target_env_file}"
	)

	# Clean up the temporary file
	rm "${tmp_env}"

	# Export final result, so the rest of the script can access variables
	set -a
	source "${target_env_file}"
	set +a

	printf "✅ Docker environment file created in the project root\n"
}

# Manage the cleanup process based on the parsed flags
handle_cleaning() {
	if [[ ${CLEAN_MODE} == "true" ]]; then
		clean_environment
	fi

	# Si només volíem netejar, sortim amb elegància
	if [[ ${ONLY_CLEAN} == "true" ]]; then
		printf "🚪 Exiting as requested (Clean mode only).\n\n"
		exit 0
	fi
}

# Clean all data related to the project
clean_environment() {
	if [[ -z ${COMPOSE_PROJECT_NAME} ]]; then
		echo "❌ COMPOSE_PROJECT_NAME not defined. Aborting clean-all.sh"
		exit 1
	fi

	PROJECT_EXISTS=$(docker ps -a -q -f "label=com.docker.compose.project=${COMPOSE_PROJECT_NAME}")

	if [[ -n ${PROJECT_EXISTS} ]]; then

		printf "\n⏳ Cleaning containers, volumes and networks: %s" "${COMPOSE_PROJECT_NAME}"

		docker compose -p "${COMPOSE_PROJECT_NAME}" down --rmi all -v --remove-orphans >/dev/null 2>&1

		printf "\r\033[K✅ Clean environment done.\n"
	fi

	# Remove dangling images
	dangling_images=$(docker images -f "dangling=true" -q)

	if [[ -n ${dangling_images} ]]; then
		printf "⏳ Removing dangling images: %s" "${dangling_images}"

		docker rmi "${dangling_images}" >/dev/null 2>&1 || true

		printf "\r\033[K✅ Clean dangling images done.\n"
	fi

	docker builder prune -f --filter "until=24h" >/dev/null 2>&1
	printf "✅ Clean build cache.\n"
}

# Check Docker config file
check_docker_config() {
	run_check "Check docker config..." \
		docker compose config
}

# Execute the command to build Docker images
# with the tag, the commit ID or the environment.
build_docker_image() {
	run_check "Building Docker images (Commit: ${COMMIT_SHA})" \
		docker compose --progress plain build
}

# Execute the command to start Docker containers
start_docker_containers() {
	run_check "Starting containers" \
		docker compose up -d
}

# Wait for service to be running
# @param $1 The service name to check
wait_for_healthy_service() {
	local service_name="$1"
	local max_retries=${2:-30}
	local count=0
	local container_id

	# Get container ID from service name
	container_id=$(docker compose ps -q "${service_name}")

	if [[ -z ${container_id} ]]; then
		echo "❌ Container not found for service ${service_name}"
		exit 1
	fi

	printf "⏳ Waiting for service %s to be healthy..." "${service_name}"

	local status=""
	local count=0

	# Inicialitzem status abans o dins del bucle per evitar sorpreses
	until [[ ${status} == "healthy" ]] || [[ ${count} -ge ${max_retries} ]]; do
		status=$(docker inspect -f '{{.State.Health.Status}}' "${container_id}" 2>/dev/null || echo "unhealthy")
		count=$((count + 1))
		printf "."
		sleep 2
	done

	if [[ ${count} -ge ${max_retries} ]]; then
		local last_state
		last_state=$(docker inspect -f '{{.State.Health.Status}}' "${container_id}")
		printf "\r\033[K❌ Service [%s] is [%s] after timeout.\n" "${service_name}" "${last_state}"
		exit 1
	fi

	printf "\r\033[K✅ Service %s is healthy.\n" "${service_name}"
}

# Check if a URL is available
# @param $1 String The URL to check
wait_for_url() {
	local url="$1"
	local max_retries=${3:-30}
	local count=0

	printf "⏳ Waiting for url %s to be ready..." "${url}"

	until curl --silent --fail "${url}" >/dev/null || [[ ${count} -eq ${max_retries} ]]; do
		sleep 2
		count=$((count + 1))
	done

	if [[ ${count} -eq ${max_retries} ]]; then
		printf "\r\033[K❌ Timed out waiting for %s.\n" "${url}"
		exit 1
	fi

	printf "\r\033[K✅ Url %s is ready.\n" "${url}"
}

# Run Laravel test using SQLite to avoid touching Postgres
run_laravel_tests() {
	if [[ ${ENVIRONMENT} == "development" ]]; then
		run_check "Executing Laravel tests..." \
			docker compose exec -T -e DB_CONNECTION=sqlite -e DB_DATABASE=':memory:' laravel-service php artisan test
	fi
}

# Display a formatted summary of the deployment status
show_deployment_summary() {
	# Using uppercase for target display
	local env_display="${ENVIRONMENT^^}"

	printf "\n\033[1;32m✅ All checks passed. Environment: %s\033[0m\n\n" "${env_display}"
	printf "🌍 Angular: %s\n" "${APP_URL}"
	printf "📡 Laravel: %s\n\n" "${APP_URL}/api"

	printf "\033[1;32m####################### STATUS OF CONTAINERS #######################\033[0m\n\n"
	docker compose -f "${COMPOSE_FILE}" ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"
	printf "\n\033[1;32m####################################################################\033[0m\n"

	printf "\n"
}

# Function to run a command and check if it succeeded,
# if not, show an error message and log it
# @param $1 String The message to display
# @param $2 Array The command to run
run_check() {
	local msg="$1"
	local output
	local status
	local log_file="docker/logs/${ENVIRONMENT}.log"

	shift

	printf "⏳ %s" "${msg}"

	set +e
	output=$("$@" 2>&1)
	status=$?
	set -e

	if [[ ${status} -eq 0 ]]; then
		printf "\r\033[K✅ %s\n" "${msg}"
	else
		local pwd
		pwd=$(pwd)

		printf "\r\033[K❌ %s\n" "${msg}"
		printf "\n----------------------------------------------\n"
		printf "\n📓 Check %s/%s for more details.\n\n" "${pwd}" "${log_file}"
		printf "%s\n\n" "----------------------------------------------"
		log "${output}"
		exit 1
	fi
}

# Log messages
log() {
	local log_file="docker/logs/${ENVIRONMENT}.log"
	TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
	MSG="[BUILD]: $*"

	mkdir -p "docker/logs"

	printf "\n%s" "${TIMESTAMP} ${MSG}" >>"${log_file}"
}
