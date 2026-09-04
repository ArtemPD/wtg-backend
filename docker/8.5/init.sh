#!/usr/bin/env bash

set -e

echo "Running migrations..."
php artisan migrate --seed --force

echo "Migrations and seeding completed successfully!"
