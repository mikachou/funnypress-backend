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
sail artisan migrate
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
```sh
sail artisan feeds:fetch
```

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

