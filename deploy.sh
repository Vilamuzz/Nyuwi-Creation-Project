sh
#!/bin/bash

echo "Building and deploying Nyuwi Creation Project..."

# Copy environment file
cp .env.docker .env

# Build and start containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 30

# Run Laravel commands
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan storage:link
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --class=IndoRegionSeeder --force
docker-compose exec app php artisan db:seed --class=UserSeeder --force
docker-compose exec app php artisan db:seed --class=CategorySeeder --force
docker-compose exec app php artisan db:seed --class=ProductSeeder --force
docker-compose exec app php artisan db:seed --class=ProfileStoreSeeder --force
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

echo "Deployment completed!"
echo "Application is running at http://localhost:8080"