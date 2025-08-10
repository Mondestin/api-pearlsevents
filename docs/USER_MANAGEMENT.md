# User Management API Documentation

## Overview

The Pearl Events API includes comprehensive user management functionality with role-based access control. Users can be either `admin` or `client` roles, each with different permissions and capabilities.

## User Roles

### Admin Users
- Can view and manage all users
- Can create, update, and delete events
- Can manage tickets for events
- Can view all bookings and statistics
- Can change user roles
- Can delete users (with restrictions)

### Client Users
- Can view and update their own profile
- Can view events and make bookings
- Can view their own bookings and statistics
- Cannot access other users' data
- Cannot manage events or tickets

## API Endpoints

### Authentication

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| POST | `/api/register` | Register a new user | Public |
| POST | `/api/login` | Login user | Public |
| GET | `/api/user` | Get authenticated user | All users |
| POST | `/api/logout` | Logout user | All users |

### User Profile Management

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/api/profile` | Get current user's profile | All users |
| PUT | `/api/profile` | Update current user's profile | All users |
| POST | `/api/change-password` | Change user password | All users |

### User Management (Admin Only)

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| POST | `/api/users` | Create new user | Admin only |
| GET | `/api/users` | List all users | Admin only |
| GET | `/api/users/{id}` | Get specific user | Admin or own profile |
| PUT | `/api/users/{id}` | Update user | Admin or own profile |
| DELETE | `/api/users/{id}` | Delete user | Admin only |
| GET | `/api/users/{id}/bookings` | Get user's bookings | Admin or own bookings |
| GET | `/api/users/{id}/events` | Get user's events | Admin only |
| GET | `/api/users/{id}/statistics` | Get user statistics | Admin only |

### Booking Management

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/api/bookings` | List user's bookings | All users |
| POST | `/api/bookings` | Create booking | Client or Admin |
| GET | `/api/bookings/{id}` | Get specific booking | Owner or Admin |
| DELETE | `/api/bookings/{id}` | Cancel booking | Owner or Admin |
| GET | `/api/bookings/upcoming` | Get upcoming bookings | All users |
| GET | `/api/bookings/past` | Get past bookings | All users |
| GET | `/api/bookings/statistics` | Get booking statistics | All users |

## Request/Response Examples

### Register User

**Request:**
```bash
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "role": "client"
}
```

**Response:**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "client",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
}
```

### Create User (Admin Only)

**Request:**
```bash
POST /api/users
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "password123",
    "role": "client"
}
```

**Response:**
```json
{
    "message": "User created successfully",
    "data": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "role": "client",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Login User

**Request:**
```bash
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "client"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
}
```

### Get User Profile

**Request:**
```bash
GET /api/profile
Authorization: Bearer {token}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "client",
        "bookings": [
            {
                "id": 1,
                "quantity": 2,
                "event": {
                    "id": 1,
                    "name": "Summer Concert",
                    "date": "2024-06-15T19:00:00.000000Z"
                },
                "ticket": {
                    "id": 1,
                    "type": "VIP",
                    "price": "150.00"
                }
            }
        ],
        "events": []
    }
}
```

### Update User Profile

**Request:**
```bash
PUT /api/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Smith",
    "email": "johnsmith@example.com"
}
```

**Response:**
```json
{
    "message": "User updated successfully",
    "data": {
        "id": 1,
        "name": "John Smith",
        "email": "johnsmith@example.com",
        "role": "client"
    }
}
```

### Change Password

**Request:**
```bash
POST /api/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "password123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

**Response:**
```json
{
    "message": "Password changed successfully"
}
```

### List All Users (Admin Only)

**Request:**
```bash
GET /api/users?role=client&search=john&per_page=10
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "role": "client",
                "created_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "total": 1,
        "per_page": 10
    }
}
```

### Get User Statistics (Admin Only)

**Request:**
```bash
GET /api/users/1/statistics
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
    "data": {
        "total_bookings": 5,
        "total_events_created": 0,
        "total_tickets_booked": 8,
        "upcoming_bookings": 3,
        "past_bookings": 2
    }
}
```

### Get User's Bookings

**Request:**
```bash
GET /api/users/1/bookings
Authorization: Bearer {token}
```

**Response:**
```json
{
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "quantity": 2,
                "event": {
                    "id": 1,
                    "name": "Summer Concert",
                    "date": "2024-06-15T19:00:00.000000Z"
                },
                "ticket": {
                    "id": 1,
                    "type": "VIP",
                    "price": "150.00"
                }
            }
        ],
        "total": 1,
        "per_page": 15
    }
}
```

### Get Booking Statistics

**Request:**
```bash
GET /api/bookings/statistics
Authorization: Bearer {token}
```

**Response:**
```json
{
    "data": {
        "total_bookings": 5,
        "total_tickets_booked": 8,
        "upcoming_bookings": 3,
        "past_bookings": 2,
        "total_spent": "1200.00"
    }
}
```

## Error Responses

### Validation Errors

```json
{
    "message": "Validation failed",
    "errors": {
        "email": [
            "The email field is required."
        ],
        "password": [
            "The password must be at least 8 characters."
        ]
    }
}
```

### Authorization Errors

```json
{
    "message": "Only admins can view all users"
}
```

### Not Found Errors

```json
{
    "message": "User not found"
}
```

## Security Features

### Role-Based Access Control
- Users can only access their own data unless they are admins
- Admins have full access to all user data
- Role changes can only be performed by admins

### Password Security
- Passwords are hashed using Laravel's built-in hashing
- Password change requires current password verification
- Minimum password length of 8 characters

### Data Protection
- Users cannot delete accounts with existing bookings or events
- Admins cannot delete their own accounts
- Email addresses must be unique

## Query Parameters

### User Listing (Admin Only)
- `role`: Filter by user role (`admin` or `client`)
- `search`: Search by name or email
- `per_page`: Number of results per page (default: 15)

### Booking Listing
- `per_page`: Number of results per page (default: 15)

## Testing

Run the user management tests:

```bash
php artisan test --filter=UserControllerTest
```

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') NOT NULL DEFAULT 'client',
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Factory Usage

### Create Test Users
```php
// Create a random user
$user = User::factory()->create();

// Create an admin user
$admin = User::factory()->admin()->create();

// Create a client user
$client = User::factory()->client()->create();

// Create multiple users
$users = User::factory()->count(10)->create();
```

## Seeding

### Default Users
The system creates default users during seeding:

- **Admin**: `admin@pearlevents.com` / `password`
- **Client**: `client@pearlevents.com` / `password`

### Demo Users
The DemoSeeder creates additional test users:

- **Admins**: `john@pearlevents.com`, `sarah@pearlevents.com`
- **Clients**: `alice@example.com`, `bob@example.com`, `carol@example.com`, `david@example.com`, `emma@example.com`

Run seeding:
```bash
php artisan db:seed --class=DemoSeeder
``` 