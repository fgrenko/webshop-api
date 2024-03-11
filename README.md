# Starting the project

### 1. Run the docker container
`docker compose up -d`

### 2. Install the required packages 
`docker compose exec php composer install`

### 3. Execute migrations
`docker compose exec php bin/console doctrine:migrations:migrate`

### 4. Execute fixtures
`docker compose exec php bin/console doctrine:fixtures:load`

### 5. Load postman collection from `\postman` and use the endpoints
