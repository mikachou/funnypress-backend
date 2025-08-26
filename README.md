# Funnypress Backend

## Overview
The Funnypress project aims to build a tool that detects humorous press article titles. This repository contains the backend of the application. It is a work in progress.

This document details the installation process on a development machine.

## Requirements
On Linux (Ubuntu 24.04), install the following software:

- Docker and docker-compose
- PHP, Composer, PHP-XML

## Install
Follow these steps to install the application:

### Clone the Repository
```sh
git clone https://github.com/mikachou/funnypress-backend
cd funnypress-backend
```

### Install Dependencies
```sh
composer install
```

### Create .env File
```sh
cp .env.example .env
```

Edit the `.env` file and fill the `APP` and `DB` sections:

```env
APP_NAME=Funnypress
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost
APP_SERVICE=funnypress.local

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### Generate Application Key
```sh
php artisan key:generate
```

### Create an Alias for Sail (if not already made)
```sh
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc
```

### Launch the Application
```sh
sail up
```

### Run Database Migrations
```sh
sail artisan migrate:fresh --seed
```

### Create an Application User
```sh
sail artisan make:filament-user
```

### Import Sources into the Application
```sh
sail artisan feeds:import resources/csv/feeds.csv
```

### Fetch Articles

Article fetching is handled asynchronously through the Laravel queue system.

```sh
sail artisan feeds:fetch
```
#### 1. Start Queue Workers
Before running the fetch command, queue workers must be started. For example, to start 8 workers:

```sh
for i in {1..8}; do sail artisan queue:work & done
```

Each worker will process queued jobs in the background. The number of workers can be adjusted depending on system resources and expected workload.

#### 2. Run the Fetch Command

Once the workers are running, execute the fetch command:

```sh
sail artisan feeds:fetch
```

This command will enqueue fetch operations into the database queue. The workers will then pick up and process these jobs.

#### 3. Logs

Logs for fetch operations are written to `storage/logs/fetch-YYYY-MM-DD.log`

Use these log files to monitor progress and debug issues.

### Update Article Scores
```sh
sail artisan articles:update-scores
```

## Usage
- Access the application in a browser at [http://localhost](http://localhost).
- Access pgAdmin at [http://localhost:5050](http://localhost:5050) with:
  - **Login:** admin@admin.fr
  - **Password:** admin
- Register the server in pgAdmin:
  - **Name:** pgsql
  - **Connection Tab:**
    - **Host Name:** pgsql
    - **Username:** sail
    - **Password:** password

