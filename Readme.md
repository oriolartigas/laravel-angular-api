# Laravel-Angular Full-Stack Application

[![Laravel](https://img.shields.io/badge/Laravel-^12.0-red.svg)](https://laravel.com)
[![Angular](https://img.shields.io/badge/Angular-^20.3-red.svg)](https://angular.io)
[![PHP](https://img.shields.io/badge/PHP-^8.2-blue.svg)](https://php.net)
[![TypeScript](https://img.shields.io/badge/TypeScript-~5.9-blue.svg)](https://typescriptlang.org)

Full-stack web application implementing RESTful API architecture with Laravel backend and Angular frontend, featuring strict Service-Repository pattern and comprehensive testing. Production & staging run on managed PostgreSQL (Supabase) with Laravel, deployed via CI/CD on GCP (production) and AWS (staging) free tiers.

**[🌐 Live Demo](https://laa-app.duckdns.org)**

## 📋 Table of Contents

- [Quick Start](#-quick-start)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
  - [Backend](#backend)
  - [Frontend](#frontend)
  - [DevOps](#devops)
- [Prerequisites](#-prerequisites)
- [Installation](#-production-installation)
- [Available Commands](#-available-commands)
- [Architecture](#-architecture)
- [Testing](#-testing)
- [CI/CD GitHub Actions](#-cicd-github-actions)
- [Docker Services](#-docker-services)
- [Project Structure](#-project-structure)
- [Code Standards](#-code-standards)
- [License](#-license)
- [Support](#-support)

## 🚀 Quick Start

Get the application up and running in three simple steps:

```bash
git clone https://github.com/oriolartigas/laravel-angular-api && cd laravel-angular-api

git checkout development

make dev
```

Note: Use make dev for local testing. The development environment includes a Postgres container with default secrets, while production expects an external database provider.

## ✨ Features

- **🏗️ Clean Architecture**: Service-Repository pattern with SOLID principles
- **🔒 Security**: Input validation, SQL injection prevention
- **📊 API-First**: RESTful endpoints with comprehensive error handling
- **🧪 Testing**: Feature and unit tests
- **🐳 Docker**: Containerized development and production environments
- **🌐 API Gateway Pattern**: Nginx Gateway for scalability, single entry point, and easy microservices expansion

## 🔧 Tech Stack

### Backend

- **PHP 8.2+** with strict type declarations
- **Laravel 12** with latest features
- **Postgres** database
- **PHPUnit** for comprehensive testing

### Frontend

- **Angular 20** with standalone components
- **TypeScript 5.9** with strict mode
- **RxJS 7.8** for reactive programming

### DevOps

- **Docker** with multi-stage builds
- **Nginx** as API Gateway (Reverse Proxy pattern)
- **Docker Compose** for orchestration
- **GitHub Actions** for CI/CD (.github/workflows/deploy.yaml)

## 📦 Prerequisites

To run this project, you only need the following installed on your local machine:

- **Docker Desktop** (or Docker Engine and Docker Compose)
- **Git**

**Note:** You do **NOT** need Node.js, PHP, Composer, or npm installed locally. All dependencies are managed within Docker containers.

## 🚀 Production installation

This application uses Docker Compose to create an isolated development environment. Dependency installation and database initialization are fully **automatic** thanks to the entrypoint scripts.

### 1. Prerequisites Check

Ensure you have **Docker** and **Git** installed.

### 2. Clone Repository

Clone the project [code](https://github.com/oriolartigas/laravel-angular-api):

```bash
git clone https://github.com/oriolartigas/laravel-angular-api

cd laravel-angular-api

git checkout main
```

Copy the .env.example file:

```bash
cp docker/envs/.env.example docker/envs/.env.production
```

Edit docker/envs/.env.production and configure:

Database Configuration (External Provider):

DB_HOST: Your external database host
DB_PORT: Database port (default: 5432)
DB_DATABASE: Database name
DB_USERNAME: Database username
DB_PASSWORD: Database password

Note: For local testing of the production build, the script automatically creates a .env.production file with development defaults.

### 3. Launch and Initialize Project (Automatic)

This command will build the images, create the required `.env` file, install Composer/NPM dependencies, generate the `APP_KEY`, and set up the Postgres database.

```bash
make prod
```

### Direct Script Execution (Optional)

While the Makefile is provided for ease of use, you can optionally call the build script directly. This allows for more granular control over the initialization process.

**Usage:** `bash docker/scripts/build.sh [parameters]`

| Parameter      | Description                                                                                         |
| -------------- | --------------------------------------------------------------------------------------------------- |
| `--env`        | Switches the build between Production and Development. Valid values are development and production. |
| `--clean`      | Performs a cleanup (remove containers, volumes, networks) before starting the new build.            |
| `--only-clean` | Only runs the cleanup process and stops the current environment.                                    |

## 🔌 Available Commands

The project includes a comprehensive Makefile with various commands for managing the development and production environments.

### Development Commands

| Command               | Description                                  |
| --------------------- | -------------------------------------------- |
| `make dev`            | Launch the standard development environment  |
| `make dev-clean`      | Clean the environment and launch development |
| `make dev-only-clean` | Only run the cleanup process for development |

### Production Commands

| Command                | Description                                  |
| ---------------------- | -------------------------------------------- |
| `make prod`            | Launch the production environment on port 80 |
| `make prod-clean`      | Clean the environment and launch production  |
| `make prod-only-clean` | Only run the cleanup process for production  |

### Help

```bash
make help
```

Shows all available commands with descriptions.

### 4. Configuration Check (Recommended)

Since the `.env` file is generated automatically, check the file in `laravel/.env` to ensure any sensitive variables (like API keys) are configured correctly.

## 🔨 Architecture

### API Gateway Pattern (Reverse Proxy)

The project implements the **API Gateway Pattern** using Nginx as a centralized reverse proxy. This architecture provides a single entry point for all client requests and enables easy scalability by allowing additional microservices to be added behind the gateway without changing client configurations.

```bash
                    [Nginx Gateway]
                    (Single Entry Point)
                           |
        +------------------+------------------+
        |                                     |
        v                                     v
[Angular Service]                    [Laravel Service]
  - DEV: ng serve :4200                - php-fpm :9000
  - PROD: nginx :8080                  - Supervisor (queue, schedule)
    (compiled static files)
```

#### Benefits of this Architecture

✅ **Single Entry Point**: All requests go through one gateway
✅ **Easy SSL/TLS**: Configure HTTPS only at the gateway level
✅ **Scalability**: Add new services (microservices) without changing clients
✅ **Load Balancing**: Distribute traffic across multiple instances
✅ **Security**: Centralized access control and rate limiting

#### Connection Logic & Ports

All environments are accessed through the **Nginx Gateway** which internally routes to the appropriate services:

| Environment | Gateway Port (External) | Angular (Internal) | Laravel (Internal) |
| ----------- | ----------------------- | ------------------ | ------------------ |
| **DEV**     | `8080`                  | `4200` (ng serve)  | `9000` (php-fpm)   |
| **PROD**    | `80`                    | `8080` (nginx)     | `9000` (php-fpm)   |

**Access URLs:**

- Development: `http://localhost:8080`
- Production: `http://localhost:80`

### Service-Repository Pattern

The Laravel backend follows a clean architecture with separation of concerns:

```bash
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Controllers   │───▶│    Services     │───▶│  Repositories   │
│  (HTTP Layer)   │    │ (Business Logic)│    │  (Data Access)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌─────────────────┐
                       │     Models      │
                       │  (Data Layer)   │
                       └─────────────────┘
```

### Key Components

- **Controllers**: Handle HTTP requests/responses and delegate to services
- **Services**: Contain business logic and coordinate between repositories
- **Repositories**: Abstract database operations with exception handling
- **Models**: Eloquent models with traits, relationships, and scopes
- **Requests**: Validate and sanitize incoming data

## 🧪 Testing

While Laravel and Angular tests are automatically executed during the development container build process, you can also run them manually:

### Laravel Tests

```bash
php artisan test
```

### Angular Tests

```bash
ng test
```

## 🚀 CI/CD GitHub Actions

The project includes automated deployment workflows in `.github/workflows/`:

**`deploy.yaml`**: Automated deployment pipeline

- Runs tests on push/pull requests
- Builds Docker images
- Deploys to configured environments

### Required GitHub Secrets

Configure the following secrets in your GitHub repository settings:

**Application Settings:**

- `APP_KEY`: Application encryption key
- `APP_URL`: URL of your application site

**Database Configuration:**

- `DB_DATABASE`: Database name
- `DB_HOST`: Database host address
- `DB_PORT`: Database port
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password
- `DB_SSL_CERT_CONTENT`: Database certificate installed automatically

**Deployment Settings:**

- `SERVER_IP`: IP address of deployment server
- `SERVER_USER`: Username for server access
- `SSH_PRIVATE_KEY`: SSH private key for server authentication

## 🐳 Docker Services

### Architecture Overview

The application uses a **multi-container architecture** with Docker Compose, implementing the API Gateway pattern for optimal separation of concerns and scalability.

### Services

#### 1. Nginx Gateway (Reverse Proxy)

- **Role**: Main entry point for all HTTP/HTTPS traffic
- **Port**: `80` (prod) / `8080` (dev)
- **Function**: Routes requests to Angular (frontend) and Laravel (API)
- **Benefits**: Centralized SSL, load balancing, easy to add new services

#### 2. Angular Service

- **Development Mode**:
  - Runs `ng serve` on port `4200`
  - Hot reload enabled for real-time development
  - Source code mounted from host for live editing
- **Production Mode**:
  - Nginx serving optimized, compiled static files
  - Runs on port `8080` (internal)
  - Immutable image with pre-built assets

#### 3. Laravel Service

- **All Environments**:
  - PHP-FPM running on port `9000`
  - Processes PHP requests via FastCGI protocol
  - Supervisor manages:
    - PHP-FPM process
    - Queue workers for background jobs
    - Task scheduler for cron jobs
- **Production Mode**:
  - Optimized Composer autoloader
  - No dev dependencies

#### 4. Postgres

- **Role**: Database server
- **Port**: `5432`
- **Features**:
  - Persistent volumes for data storage
  - Health checks for container orchestration
  - Automatic initialization on first run

### Container Communication

```bash
External Request → Nginx Gateway :80
                      ↓
    ┌─────────────────┴─────────────────┐
    │                                   │
    ▼                                   ▼
Angular :4200/:8080              Laravel :9000
    │                                   │
    └───────────────┬───────────────────┘
                    ▼
                Postgres :5432
```

All services communicate through a Docker bridge network (`app-network`), ensuring isolation and security.

## 📁 Project Structure

```bash
laravel-angular-api/
├── laravel/              # Laravel API application
│   ├── app/
│   │   ├── Contracts/    # Services and repositories contracts
│   │   ├── Http/         # Controllers and requests
│   │   ├── Services/     # Business logic layer
│   │   ├── Repositories/ # Data access layer
│   │   └── Models/       # Eloquent models
│   ├── tests/            # PHPUnit tests
│   └── database/         # Migrations and seeders
├── angular/              # Angular SPA application
│   ├── src/app/          # Angular components and services
│   ├── src/testing       # Angular testing factories and services
│   └── proxy.conf.json   # API proxy configuration (development)
└── docker                # Scripts, dockerfiles and configs used to build containers
```

## 📝 Code Standards

- **PHP**: Follow PSR-12 coding standards
- **TypeScript**: Use Angular style guide
- **Testing**: Write comprehensive feature and unit tests
- **Documentation**: Document all public methods and classes

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:

- Create an issue in the [repository](https://github.com/oriolartigas/laravel-angular-api)
- Check existing documentation
- Review test examples for usage patterns
