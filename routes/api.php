<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ContactController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Contact us (public)
Route::post('/contact-us', [ContactController::class, 'sendMailtoAdmin']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User profile and management
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'update']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    
    // Users (admin only)
    Route::apiResource('users', UserController::class);
    Route::get('/users/{user}/bookings', [UserController::class, 'bookings']);
    Route::get('/users/{user}/events', [UserController::class, 'events']);
    Route::get('/users/{user}/statistics', [UserController::class, 'statistics']);
    
    // Events (admin only for create/update/delete)
    Route::apiResource('events', EventController::class);
    Route::get('/events/search', [EventController::class, 'search']);
    
    // Event pictures (admin only)
    Route::post('/events/{event}/pictures', [EventController::class, 'addPicture']);
    Route::delete('/events/{event}/pictures', [EventController::class, 'removePicture']);
    Route::post('/events/{event}/upload-pictures', [EventController::class, 'uploadPictures']);
    
    // Tickets (nested under events)
    Route::apiResource('events.tickets', TicketController::class);
    
    // Bookings
    Route::apiResource('bookings', BookingController::class);
    Route::get('/events/{event}/bookings', [BookingController::class, 'eventBookings']);
    Route::get('/bookings/upcoming', [BookingController::class, 'upcomingBookings']);
    Route::get('/bookings/past', [BookingController::class, 'pastBookings']);
    Route::get('/bookings/statistics', [BookingController::class, 'statistics']);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Pearl Events API is running',
        'timestamp' => now()
    ]);
}); 