#!/usr/bin/env bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/../../" && pwd)"

export ROOT_DIR

# shellcheck source=/dev/null
source "${SCRIPT_DIR}/docker-common.sh"

# Parse all arguments
parse_arguments "$@"

# Generate env_files in local development/production
if [[ ${GITHUB_ACTIONS} != "true" ]]; then
	merge_docker_compose_files
	generate_env_files
fi

# Clean environment based on the parsed arguments
handle_cleaning

cd "${ROOT_DIR}"

printf "\n🚀 Starting setup for %s...\n\n" "${COMPOSE_PROJECT_NAME}"

# Build docker
check_docker_config
build_docker_image
start_docker_containers

# Wait for containers
if [[ ${ENVIRONMENT} == "development" ]]; then
	wait_for_healthy_service "postgres-service"
fi

wait_for_healthy_service "laravel-service"
wait_for_healthy_service "angular-service"
wait_for_healthy_service "nginx-service"

wait_for_url "${APP_URL}"

# Run laravel tests only in development
run_laravel_tests

# Print a summary of the build
show_deployment_summary
