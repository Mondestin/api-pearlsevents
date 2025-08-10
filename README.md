# Pearl Events API

A modern RESTful API for event management and ticket booking built with Laravel 12.

## Features

- **User Authentication** - Register, login, and manage user profiles with roles (admin/client)
- **Event Management** - Create, read, update, and delete events (admin only)
- **Ticket Management** - Create and manage tickets for events (admin only)
- **Booking System** - Clients can book tickets for events
- **Role-based Access** - Different permissions for admins and clients
- **Search & Filtering** - Search events by name, description, or location
- **Pagination** - Efficient data pagination for large datasets
- **API Documentation** - Comprehensive API endpoints

## Technology Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Database**: SQLite (configurable for production)
- **Authentication**: Laravel Sanctum
- **API**: RESTful JSON API
- **Validation**: Laravel's built-in validation
- **Testing**: PHPUnit

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd pearleventsApi
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

## Database Factories & Seeders

The project includes comprehensive factories and seeders for testing and development:

### Factories
- **UserFactory** - Creates users with roles (admin/client)
- **EventFactory** - Creates events with realistic data
- **TicketFactory** - Creates tickets with different types and prices
- **BookingFactory** - Creates bookings with various quantities

### Seeders
- **UserSeeder** - Creates default admin and client users
- **EventSeeder** - Creates sample events with descriptions
- **TicketSeeder** - Creates tickets for each event
- **BookingSeeder** - Creates sample bookings
- **DemoSeeder** - Creates a complete demo dataset

### Seeding Options

**Full demo dataset:**
```bash
php artisan db:seed --class=DemoSeeder
```

**Individual seeders:**
```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=EventSeeder
php artisan db:seed --class=TicketSeeder
php artisan db:seed --class=BookingSeeder
```

**Default users created:**
- **Admin**: `admin@pearlevents.com` / `password`
- **Client**: `client@pearlevents.com` / `password`

**Demo users (DemoSeeder):**
- **Admins**: `john@pearlevents.com`, `sarah@pearlevents.com`
- **Clients**: `alice@example.com`, `bob@example.com`, `carol@example.com`, `david@example.com`, `emma@example.com`

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register a new user |
| POST | `/api/login` | Login user |
| GET | `/api/user` | Get authenticated user |
| POST | `/api/logout` | Logout user |

### Events

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/api/events` | List all events | All users |
| POST | `/api/events` | Create a new event | Admin only |
| GET | `/api/events/{id}` | Get specific event | All users |
| PUT | `/api/events/{id}` | Update event | Admin only |
| DELETE | `/api/events/{id}` | Delete event | Admin only |
| GET | `/api/events/search` | Search events | All users |

### Tickets

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/api/events/{event}/tickets` | List tickets for event | All users |
| POST | `/api/events/{event}/tickets` | Create ticket for event | Admin only |
| GET | `/api/events/{event}/tickets/{id}` | Get specific ticket | All users |
| PUT | `/api/events/{event}/tickets/{id}` | Update ticket | Admin only |
| DELETE | `/api/events/{event}/tickets/{id}` | Delete ticket | Admin only |

### Bookings

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/api/bookings` | List user's bookings | Client only |
| POST | `/api/bookings` | Create a booking | Client only |
| GET | `/api/bookings/{id}` | Get specific booking | Owner only |
| DELETE | `/api/bookings/{id}` | Cancel booking | Owner only |
| GET | `/api/events/{event}/bookings` | List bookings for event | Admin only |

### Health Check

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/health` | API health status |

## Authentication

The API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

## User Roles

- **Admin**: Can create, update, and delete events and tickets. Can view all bookings.
- **Client**: Can view events and tickets, and make bookings.

## Request Examples

### Register an Admin User
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "admin"
  }'
```

### Create an Event
```bash
curl -X POST http://localhost:8000/api/events \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tech Conference 2024",
    "description": "Annual technology conference",
    "location": "Convention Center",
    "date": "2024-06-15 09:00:00"
  }'
```

### Create a Ticket
```bash
curl -X POST http://localhost:8000/api/events/1/tickets \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "VIP",
    "price": 100.00,
    "quantity": 50
  }'
```

### Make a Booking
```bash
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "ticket_id": 1,
    "quantity": 2
  }'
```

## Database Schema

### Users Table
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `role` - User role (admin/client)
- `email_verified_at` - Email verification timestamp
- `remember_token` - Remember me token
- `created_at`, `updated_at` - Timestamps

### Events Table
- `id` - Primary key
- `user_id` - Foreign key to users (event creator)
- `name` - Event name
- `description` - Event description
- `location` - Event location
- `date` - Event date/time
- `created_at`, `updated_at` - Timestamps

### Tickets Table
- `id` - Primary key
- `event_id` - Foreign key to events
- `type` - Ticket type (VIP, Regular, etc.)
- `price` - Ticket price
- `quantity` - Total number of tickets available
- `created_at`, `updated_at` - Timestamps

### Bookings Table
- `id` - Primary key
- `user_id` - Foreign key to users (client)
- `event_id` - Foreign key to events
- `ticket_id` - Foreign key to tickets
- `quantity` - Number of tickets booked
- `created_at`, `updated_at` - Timestamps

## Development

### Running Tests
```bash
php artisan test
```

### Code Formatting
```bash
./vendor/bin/pint
```

### Development Server
```bash
composer dev
```

### Factory Usage Examples

**Create users:**
```php
// Create a random user
$user = User::factory()->create();

// Create an admin user
$admin = User::factory()->admin()->create();

// Create multiple client users
$clients = User::factory()->client()->count(5)->create();
```

**Create events:**
```php
// Create a random event
$event = Event::factory()->create();

// Create an upcoming event
$upcomingEvent = Event::factory()->upcoming()->create();

// Create multiple events
$events = Event::factory()->count(10)->create();
```

**Create tickets:**
```php
// Create a VIP ticket
$vipTicket = Ticket::factory()->vip()->create();

// Create a free ticket
$freeTicket = Ticket::factory()->free()->create();

// Create tickets for a specific event
$tickets = Ticket::factory()->count(3)->create(['event_id' => $event->id]);
```

**Create bookings:**
```php
// Create a single ticket booking
$booking = Booking::factory()->single()->create();

// Create a group booking
$groupBooking = Booking::factory()->group()->create();
```

## Production Deployment

1. Set environment variables for production
2. Configure database connection
3. Run migrations
4. Set up proper authentication
5. Configure CORS if needed
6. Set up proper logging

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# api-pearlsevents
