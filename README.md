# Starting the project

### 1. Run the docker container
`docker compose up -d`


You don't have to manually install packages using Composer because the Dockerfile 
takes care of that. Additionally, the frankenphp script ensures that all migrations 
are executed. Please wait until all the scripts finish executing.

### 2. Load fixtures
`docker compose exec php bin/console doctrine:fixtures:load`

### 3. Load postman collection from `\postman` and use the endpoints
