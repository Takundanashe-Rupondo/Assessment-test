# Laravel Backend API - QA Assessment Project

## Overview

This is a Laravel-based REST API application for managing users, products, and orders. The application includes user authentication, CRUD operations, and various business logic endpoints.

## Features

- User Authentication (Registration, Login, Logout)
- User Management (CRUD operations)
- Product Management (CRUD operations)
- Order Management (Create orders, View orders)
- API endpoints for all operations
- Basic admin functionality

## Setup Instructions

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   - Update `.env` with your database credentials:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=qa_assessment
     DB_USERNAME=root
     DB_PASSWORD=
     ```

4. **Run Migrations and Seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start Development Server**
   ```bash
   php artisan serve
   ```

   The API will be available at `http://localhost:8000`

## API Endpoints

### Authentication

- `POST /api/register` - Register a new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (requires authentication)
- `GET /api/user` - Get authenticated user details

### Users

- `GET /api/users` - List all users (admin only)
- `GET /api/users/{id}` - Get user by ID
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

### Products

- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get product by ID
- `POST /api/products` - Create product (admin only)
- `PUT /api/products/{id}` - Update product (admin only)
- `DELETE /api/products/{id}` - Delete product (admin only)

### Orders

- `GET /api/orders` - List user's orders
- `GET /api/orders/{id}` - Get order by ID
- `POST /api/orders` - Create new order
- `PUT /api/orders/{id}` - Update order status (admin only)

## Default Test Data

After running seeders, you'll have:

- **Admin User**: 
  - Email: `admin@test.com`
  - Password: `password`
  
- **Regular User**: 
  - Email: `user@test.com`
  - Password: `password`

- **Products**: 10 sample products

## Testing Notes

- Use Postman or similar tool for API testing
- Authentication uses Laravel Sanctum tokens
- Include `Authorization: Bearer {token}` header for protected routes

## GitHub Actions CI/CD

This project includes automated testing and deployment workflows using GitHub Actions.

### Required Secrets

Add these secrets to your GitHub repository settings:

- `DEPLOY_HOST` - Your server IP address or hostname
- `DEPLOY_USER` - SSH username for deployment
- `DEPLOY_KEY` - SSH private key for server access
- `SLACK_WEBHOOK` - (Optional) Slack webhook for notifications

### Workflows

#### 1. Tests Workflow (`.github/workflows/tests.yml`)
- Triggers on push/PR to main and develop branches
- Runs PHPUnit tests on PHP 8.1 and 8.2
- Uses MySQL service for database testing
- Generates code coverage reports with Codecov

#### 2. Deploy Workflow (`.github/workflows/deploy.yml`)
- Triggers on push to main branch
- Installs production dependencies
- Optimizes Laravel caches
- Deploys to production server via SSH
- Runs migrations and restarts services

#### 3. Full CI/CD Pipeline (`.github/workflows/ci.yml`)
- Comprehensive pipeline with multiple jobs
- Tests on PHP 8.1, 8.2, and 8.3
- Code quality checks (Laravel Pint, PHPStan)
- Security audits
- Deployment to production
- Slack notifications

### Docker Support

The project includes Docker configuration for containerized deployment:

#### Quick Start with Docker
```bash
# Build and start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

#### Docker Services
- **app** - PHP-FPM 8.2 service
- **webserver** - Nginx web server
- **db** - MySQL 8.0 database
- **redis** - Redis cache service

### Manual Deployment

Use the provided deployment script for manual deployments:

```bash
chmod +x deploy.sh
./deploy.sh
```

### Environment Setup for CI

The GitHub Actions workflows automatically configure the testing environment with:
- MySQL database service
- Required PHP extensions
- Optimized composer caching
- Proper Laravel environment variables

## Development Workflow

1. **Feature Development**
   - Create feature branch from develop
   - Make changes and commit
   - Push to trigger tests

2. **Testing**
   - GitHub Actions runs automated tests
   - Code coverage reports generated
   - Code quality checks performed

3. **Deployment**
   - Merge to develop triggers additional testing
   - Merge to main triggers production deployment
   - Automated notifications sent to Slack

## Local Development with Docker

For local development that matches the production environment:

```bash
# Copy environment file
cp .env.example .env

# Start services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Run tests
docker-compose exec app php artisan test

# Access application
curl http://localhost:8080/api/products
```

