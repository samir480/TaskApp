# TaskApp

TaskApp is a simple task management application built with Laravel. It allows users to create and view tasks,notes, and attachments.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Testing](#testing)
- [API Endpoints](#api-endpoints)

## Requirements

To run this project, ensure your server meets the following requirements:

- **PHP**: 8.2+ or higher
- **Composer**: Latest version

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/samir480/TaskApp.git
2. Install dependencies
   ```bash
   cd TaskApp
   composer install
   npm install
3. Create a .env file
   ```bash
   cp .env.example .env
4. Generate an application key
   ```bash
   php artisan key:generate
5. Configure your database:
   ```bash
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_username
    DB_PASSWORD=your_database_password
6. Run migrations:
    ```bash
    php artisan migrate
7. Run the development server:
   ```bash
   php artisan serve

## Testing

To run the test suite, execute:

    php artisan test

## API Endpoints

1. Authentication
   
   Register User (POST)
   ```bash
   /api/register
    ```
   ```bash
   {
    "name":"Samir Shaikh",
    "email":"samir@admin.com",
    "password":"Admin@123",
    "password_confirmation":"Admin@123"
    }
   ```
   Login User (POST)
   ```bash
   /api/login
   ```
   ```bash
   {
    "email":"samir@admin.com",
    "password":"Admin@123"
    }
   ```
   Logout User (POST)
   ```bash
   /api/logout
   ```
3. Tasks
    Create Task (POST)
   ```bash
   /api/tasks/create
    ```
   ```bash
    {
      "subject": "S1",
      "description": "D1",
      "start_date": "20-08-2008",
      "due_date": "22-08-2008",
      "status": "new",
      "priority": "high",
      "notes": [
        {
          "subject": "ns1",
          "note": "n1",
          "attachments": [
            "files"
          ]
        },
        {
          "subject": "ns2",
          "note": "n2",
          "attachments": [
            "files"
          ]
        }
      ]
    }

   ```
    View Tasks (GET)
   ```bash
   /api/tasks
    ```
   ```bash
    {
    "filter": {
        "status": "",
        "due_date": "",
        "priority": ""
      }
   }
   ```






