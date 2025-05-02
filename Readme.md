

# Web App to List Events using Civic Plus API

- Backend: PHP 8.3
-  Frontend: Vue js

## Backend Setup

1. Copy the `config.example.php` file to `config.php`:
   ```bash
   cp backend/config.example.php backend/config.php
   ```

2. Fill in the variables in `config.php` with your API credentials.

3. Start the backend services using Docker Compose:
   ```bash
   docker-compose up -d
   ```
4. Install dependencies:
   ```bash
   docker compose exec php-fpm composer install
   ```

## Frontend Setup

1. Navigate to the frontend folder `frontend/events-app-frontend`:
   ```bash
   cd frontend/events-app-frontend
   ```

2. Install the dependencies:
   ```bash
   npm install
   ```

3. Start the development server:
   ```bash
   npm run dev
   ```

4. Update the CORS setting using the frontend IP/Port
   backend/config.php -> ALLOWED_CORS