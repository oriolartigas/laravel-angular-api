#!/usr/bin/env bash

set -e

if [[ ${APP_ENV} == "development" ]]; then
	# Install dependencies if missing
	if [[ -f package.json ]]; then
		npm ci
	fi
fi

if [[ -z $1 ]]; then
	echo "ERROR: No command (CMD) provided. Exiting to avoid loop."
	exit 1
fi

echo "Starting Angular development server..."
exec "$@"
