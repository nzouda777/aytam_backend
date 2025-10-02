# Laravel Docker Setup

This project is set up with Docker and includes the following services:
- PHP 8.2 with FPM
- Nginx
- MySQL 8.0
- phpMyAdmin

## Prerequisites

- Docker
- Docker Compose

## Getting Started

1. Copy the Docker environment file:
   ```bash
   cp .env.docker .env
   ```

2. Build and start the containers:
   ```bash
   docker-compose up -d --build
   ```

3. Install PHP dependencies:
   ```bash
   docker-compose exec app composer install
   ```

4. Generate application key:
   ```bash
   docker-compose exec app php artisan key:generate
   ```

5. Run database migrations:
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. (Optional) Install NPM dependencies and compile assets:
   ```bash
   docker-compose exec app npm install
   docker-compose exec app npm run dev
   ```

## Accessing the Application

- **Web Application**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
  - Server: db
  - Username: laravel
  - Password: secret
  - Root Username: root
  - Root Password: root

## Useful Commands

- Stop all containers:
  ```bash
  docker-compose down
  ```

- View logs:
  ```bash
  docker-compose logs -f
  ```

- Run Artisan commands:
  ```bash
  docker-compose exec app php artisan [command]
  ```

- Run NPM commands:
  ```bash
  docker-compose exec app npm [command]
  ```

## Project Structure

- `docker/` - Contains Docker configuration files
  - `nginx/` - Nginx configuration
  - `mysql/` - MySQL configuration
- `.env.docker` - Example environment file for Docker
- `docker-compose.yml` - Docker Compose configuration
- `Dockerfile` - PHP-FPM container configuration
