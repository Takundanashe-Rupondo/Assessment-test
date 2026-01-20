# Flutter Mobile App - QA Assessment Project

## Overview

This is a Flutter mobile application that connects to the Laravel backend API. The app allows users to browse products, view orders, and manage their account.

## Features

- User Authentication (Login, Logout)
- Product Listing
- Product Details
- Order History
- User Profile
- API Integration

## Setup Instructions

1. **Install Flutter Dependencies**
   ```bash
   flutter pub get
   ```

2. **Configure API Base URL**
   - Open `lib/config/api_config.dart`
   - Update `baseUrl` to point to your Laravel backend (default: `http://localhost:8000`)

3. **Run the App**
   ```bash
   flutter run
   ```

   For iOS:
   ```bash
   flutter run -d ios
   ```

   For Android:
   ```bash
   flutter run -d android
   ```

## Default Test Credentials

- **Email**: `user@test.com`
- **Password**: `password`

## Project Structure

```
lib/
├── config/
│   └── api_config.dart          # API configuration
├── models/
│   ├── user.dart                 # User model
│   ├── product.dart              # Product model
│   └── order.dart                # Order model
├── services/
│   └── api_service.dart          # API service
├── screens/
│   ├── login_screen.dart         # Login screen
│   ├── products_screen.dart      # Product listing
│   ├── product_detail_screen.dart # Product details
│   ├── orders_screen.dart        # Order history
│   └── profile_screen.dart       # User profile
├── widgets/
│   └── product_card.dart         # Product card widget
└── main.dart                     # App entry point
```


