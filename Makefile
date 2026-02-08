# Default variables
BUILD_SCRIPT=bash docker/scripts/build.sh

.PHONY: help dev dev-clean dev-only-clean \
		stg stg-clean stg-only-clean \
		prod prod-clean prod-only-clean

# Only dev build
dev: ## Launch the standard development environment
	@$(BUILD_SCRIPT) --env=development

# Dev build + clean
dev-clean: ## Clean the environment and Launch development
	@$(BUILD_SCRIPT) --env=development --clean

# Clean dev
dev-only-clean: ## Only run the cleanup process for development
	@$(BUILD_SCRIPT) --env=development --only-clean

##@ Staging Environment (Prod)

# Only staging build
stg: ## Launch the staging environment
	@$(BUILD_SCRIPT) --env=staging

# Staging build + clean
stg-clean: ## Clean the environment and staging
	@$(BUILD_SCRIPT) --env=staging --clean

# Only clean staging
stg-only-clean: ## Only run the cleanup process for staging
	@$(BUILD_SCRIPT) --env=staging --only-clean

##@ Production Environment (Prod)

# Only production build
prod: ## Launch the real production environment on port 80
	@$(BUILD_SCRIPT) --env=production

# Production build + clean
prod-clean: ## Clean the environment and Launch real production
	@$(BUILD_SCRIPT) --env=production --clean

# Only clean production
prod-only-clean: ## Only run the cleanup process for production
	@$(BUILD_SCRIPT) --env=production --only-clean

# Show this help message
help:
	@$(HELP_COMMAND)

HELP_COMMAND=@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<environment>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)
