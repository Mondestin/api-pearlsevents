# Logging System Documentation

## Overview

The Pearl Events API includes comprehensive logging for user management and booking operations. The logging system tracks user actions, errors, security events, and system performance to provide better monitoring and debugging capabilities.

## Log Levels Used

### Info Level
- Successful user actions (profile updates, bookings, etc.)
- Administrative actions (user management, role changes)
- System operations (user listings, statistics)

### Warning Level
- Unauthorized access attempts
- Validation failures
- Security-related issues
- Business rule violations

### Error Level
- System exceptions and errors
- Database transaction failures
- Critical system issues

## Logged Events

### User Management Events

#### User Listing
```php
// Info: Users listed successfully
Log::info('Users listed successfully', [
    'admin_id' => $request->user()->id,
    'admin_email' => $request->user()->email,
    'total_users' => $users->count(),
    'filters' => [
        'role' => $request->get('role'),
        'search' => $request->get('search')
    ]
]);

// Warning: Unauthorized access attempt
Log::warning('Unauthorized access attempt to list users', [
    'user_id' => $request->user()->id,
    'user_email' => $request->user()->email,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent()
]);
```

#### User Creation
```php
// Info: User created successfully by admin
Log::info('User created successfully by admin', [
    'admin_id' => $request->user()->id,
    'admin_email' => $request->user()->email,
    'new_user_id' => $user->id,
    'new_user_email' => $user->email,
    'new_user_role' => $user->role,
    'ip_address' => $request->ip()
]);

// Warning: Non-admin attempt to create user
Log::warning('Non-admin attempt to create user', [
    'requesting_user_id' => $request->user()->id,
    'requesting_user_email' => $request->user()->email,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent()
]);
```

#### User Profile Operations
```php
// Info: User profile viewed
Log::info('User profile viewed', [
    'viewer_id' => $request->user()->id,
    'viewer_email' => $request->user()->email,
    'target_user_id' => $user->id,
    'target_user_email' => $user->email,
    'is_admin_view' => $request->user()->isAdmin()
]);

// Info: User profile updated
Log::info('User profile updated successfully', [
    'target_user_id' => $user->id,
    'target_user_email' => $user->email,
    'updated_by' => $request->user()->id,
    'updated_by_email' => $request->user()->email,
    'changes' => array_diff_assoc($updatedUser->only(['name', 'email', 'role']), $originalData),
    'ip_address' => $request->ip()
]);
```

#### Role Management
```php
// Info: User role changed by admin
Log::info('User role changed by admin', [
    'target_user_id' => $user->id,
    'target_user_email' => $user->email,
    'old_role' => $user->role,
    'new_role' => $request->role,
    'admin_id' => $request->user()->id,
    'admin_email' => $request->user()->email,
    'ip_address' => $request->ip()
]);

// Warning: Non-admin attempt to change role
Log::warning('Non-admin attempt to change user role', [
    'requesting_user_id' => $request->user()->id,
    'requesting_user_email' => $request->user()->email,
    'target_user_id' => $user->id,
    'requested_role' => $request->role,
    'ip_address' => $request->ip()
]);
```

#### User Deletion
```php
// Info: User deleted successfully
Log::info('User deleted successfully', [
    'admin_id' => $request->user()->id,
    'admin_email' => $request->user()->email,
    'deleted_user' => $userData,
    'ip_address' => $request->ip()
]);

// Warning: Attempt to delete user with existing data
Log::warning('Attempt to delete user with existing data', [
    'admin_id' => $request->user()->id,
    'admin_email' => $request->user()->email,
    'target_user_id' => $user->id,
    'target_user_email' => $user->email,
    'booking_count' => $bookingCount,
    'event_count' => $eventCount,
    'ip_address' => $request->ip()
]);
```

#### Password Management
```php
// Info: User password changed successfully
Log::info('User password changed successfully', [
    'user_id' => $user->id,
    'user_email' => $user->email,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent()
]);

// Warning: Password change failed
Log::warning('Password change failed - incorrect current password', [
    'user_id' => $user->id,
    'user_email' => $user->email,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent()
]);
```

### Booking Events

#### Booking Creation
```php
// Info: Booking created successfully
Log::info('Booking created successfully', [
    'booking_id' => $booking->id,
    'user_id' => $userId,
    'ticket_id' => $ticket->id,
    'event_id' => $ticket->event_id,
    'quantity' => $request->quantity,
    'booked_by_admin' => $request->user()->isAdmin(),
    'admin_id' => $request->user()->isAdmin() ? $request->user()->id : null,
    'ip_address' => $request->ip()
]);

// Warning: Insufficient tickets
Log::warning('Insufficient tickets for booking', [
    'user_id' => $request->user()->id,
    'ticket_id' => $ticket->id,
    'requested_quantity' => $request->quantity,
    'available_tickets' => $ticket->available_tickets,
    'ip_address' => $request->ip()
]);
```

### Error Logging

#### General Error Pattern
```php
// Error: General system errors
Log::error('Error [operation_name]', [
    'user_id' => $request->user()->id,
    'user_email' => $request->user()->email,
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
    'ip_address' => $request->ip(),
    'request_data' => $request->except(['password']), // Exclude sensitive data
    'user_agent' => $request->userAgent()
]);
```

#### Database Transaction Errors
```php
// Error: Database transaction failures
Log::error('Database error during booking creation', [
    'user_id' => $request->user()->id,
    'ticket_id' => $ticket->id,
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
    'ip_address' => $request->ip()
]);
```

## Log Data Structure

### Common Fields
- `user_id`: ID of the user performing the action
- `user_email`: Email of the user performing the action
- `ip_address`: IP address of the request
- `user_agent`: User agent string
- `error`: Error message (for error logs)
- `trace`: Stack trace (for error logs)

### Security-Related Fields
- `requesting_user_id`: ID of user making the request
- `target_user_id`: ID of user being acted upon
- `admin_id`: ID of admin performing admin actions
- `is_admin_view`: Boolean indicating admin access

### Business Logic Fields
- `booking_id`: ID of created booking
- `ticket_id`: ID of ticket being booked
- `event_id`: ID of event being booked
- `quantity`: Number of tickets/items
- `changes`: Array of changed fields (for updates)
- `statistics`: Array of user statistics

## Log Configuration

### Laravel Log Configuration
The logging system uses Laravel's built-in logging with the following configuration:

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'daily'],
        'ignore_exceptions' => false,
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
],
```

### Environment Variables
```env
LOG_CHANNEL=stack
LOG_LEVEL=debug
LOG_DAYS=14
```

## Monitoring and Analysis

### Log Analysis Tools
- **Laravel Telescope**: For development debugging
- **ELK Stack**: For production monitoring
- **Custom log parsers**: For specific business metrics

### Key Metrics to Monitor
1. **Security Events**: Unauthorized access attempts
2. **Error Rates**: System failures and exceptions
3. **User Activity**: Profile updates, bookings, role changes
4. **Performance**: Database transaction times
5. **Business Metrics**: Booking success rates, user engagement

### Alert Thresholds
- **High Error Rate**: >5% error rate in 5 minutes
- **Security Alerts**: Multiple failed login attempts
- **Performance Issues**: Response times >2 seconds
- **Business Alerts**: Booking failures, user deletion attempts

## Best Practices

### Data Privacy
- Never log sensitive data (passwords, tokens)
- Use `$request->except(['password'])` for request data
- Hash or anonymize personal information when needed

### Performance
- Use structured logging for better parsing
- Include relevant context in each log entry
- Avoid logging in tight loops

### Security
- Log all authentication and authorization events
- Track IP addresses and user agents
- Monitor for suspicious patterns

### Debugging
- Include stack traces for errors
- Log request data for debugging
- Use consistent log message formats

## Example Log Queries

### Find Security Events
```bash
grep "Unauthorized access attempt" storage/logs/laravel.log
```

### Find User Management Events
```bash
grep "User profile updated successfully" storage/logs/laravel.log
```

### Find Booking Events
```bash
grep "Booking created successfully" storage/logs/laravel.log
```

### Find Errors by User
```bash
grep "user_id.*123" storage/logs/laravel.log | grep "ERROR"
```

## Integration with Monitoring Tools

### Laravel Telescope
```php
// config/telescope.php
'watchers' => [
    Watchers\LogWatcher::class => [
        'enabled' => env('TELESCOPE_LOG_WATCHER', true),
        'level' => env('TELESCOPE_LOG_LEVEL', 'info'),
    ],
],
```

### External Monitoring
- **Sentry**: For error tracking
- **New Relic**: For performance monitoring
- **Datadog**: For comprehensive monitoring
- **CloudWatch**: For AWS-based monitoring

## Log Retention Policy

### Development
- Keep logs for 7 days
- Debug level logging enabled

### Production
- Keep logs for 30 days
- Info level and above
- Daily log rotation
- Compressed archives after 7 days

### Security Logs
- Keep for 90 days minimum
- Real-time monitoring
- Immediate alerts for suspicious activity 