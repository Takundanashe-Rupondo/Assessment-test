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

