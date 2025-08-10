<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Store a newly created user (admin only)
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Must be unique. Example: john@example.com
     * @bodyParam password string required The password for the user. Minimum 8 characters. Example: password123
     * @bodyParam role string The role of the user (admin or client). Defaults to client. Example: client
     * 
     * @response 201 {
     *   "message": "User created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "role": "client",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * 
     * @response 403 {
     *   "message": "Only admins can create users"
     * }
     * 
     * @response 422 {
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email has already been taken."],
     *     "password": ["The password must be at least 8 characters."]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Only admins can create users
            if (!$request->user()->isAdmin()) {
                Log::warning('Non-admin attempt to create user', [
                    'requesting_user_id' => $request->user()->id,
                    'requesting_user_email' => $request->user()->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Only admins can create users'
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'sometimes|in:admin,client',
            ]);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'client', // Default to client if not specified
            ];

            $user = User::create($userData);

            Log::info('User created successfully by admin', [
                'admin_id' => $request->user()->id,
                'admin_email' => $request->user()->email,
                'new_user_id' => $user->id,
                'new_user_email' => $user->email,
                'new_user_role' => $user->role,
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating user', [
                'admin_id' => $request->user()->id,
                'admin_email' => $request->user()->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password']),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to create user'
            ], 500);
        }
    }

    /**
     * Display a listing of users (admin only)
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @queryParam role string Filter users by role (admin or client). Example: client
     * @queryParam search string Search users by name or email. Example: john
     * @queryParam per_page integer Number of results per page. Example: 15
     * 
     * @response {
     *   "data": {
     *     "data": [
     *       {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john@example.com",
     *         "role": "client",
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ],
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 1
     *   }
     * }
     * 
     * @response 403 {
     *   "message": "Only admins can view all users"
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Only admins can view all users
            if (!$request->user()->isAdmin()) {
                Log::warning('Unauthorized access attempt to list users', [
                    'user_id' => $request->user()->id,
                    'user_email' => $request->user()->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Only admins can view all users'
                ], 403);
            }

            // Get query parameters for filtering
            $query = User::query();

            // Filter by role if provided
            if ($request->has('role') && in_array($request->role, ['admin', 'client'])) {
                $query->where('role', $request->role);
            }

            // Search by name or email if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->get();
            
            Log::info('Users listed successfully', [
                'admin_id' => $request->user()->id,
                'admin_email' => $request->user()->email,
                'total_users' => $users->count(),
                'filters' => [
                    'role' => $request->get('role'),
                    'search' => $request->get('search')
                ]
            ]);

            return response()->json([
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error listing users', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to list users'
            ], 500);
        }
    }

    /**
     * Display the specified user
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @urlParam user integer required The ID of the user. Example: 1
     * 
     * @response {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "role": "client",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z",
     *     "bookings": [
     *       {
     *         "id": 1,
     *         "quantity": 2,
     *         "event": {
     *           "id": 1,
     *           "name": "Summer Concert",
     *           "date": "2024-06-15T19:00:00.000000Z"
     *         }
     *       }
     *     ],
     *     "events": []
     *   }
     * }
     * 
     * @response 403 {
     *   "message": "Unauthorized to view this user"
     * }
     * 
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\User]."
     * }
     */
    public function show(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user);
        try {
            // Users can only view their own profile, admins can view any user
            if ($user->id !== $request->user()->id && !$request->user()->isAdmin()) {
                Log::warning('Unauthorized access attempt to view user profile', [
                    'requesting_user_id' => $request->user()->id,
                    'requesting_user_email' => $request->user()->email,
                    'target_user_id' => $user->id,
                    'target_user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Unauthorized to view this user'
                ], 403);
            }

            $userData = $user->load(['bookings.event', 'events']);
            
            Log::info('User profile viewed', [
                'viewer_id' => $request->user()->id,
                'viewer_email' => $request->user()->email,
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'is_admin_view' => $request->user()->isAdmin()
            ]);

            return response()->json([
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing user profile', [
                'requesting_user_id' => $request->user()->id,
                'requesting_user_email' => $request->user()->email,
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to view user profile'
            ], 500);
        }
    }

    /**
     * Update the specified user's profile
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @urlParam user integer required The ID of the user. Example: 1
     * 
     * @bodyParam name string The name of the user. Example: John Smith
     * @bodyParam email string The email of the user. Must be unique. Example: john.smith@example.com
     * @bodyParam password string The new password for the user. Minimum 8 characters. Example: newpassword123
     * @bodyParam role string The role of the user (admin or client). Only admins can change roles. Example: client
     * 
     * @response {
     *   "message": "User updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Smith",
     *     "email": "john.smith@example.com",
     *     "role": "client",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * 
     * @response 403 {
     *   "message": "Unauthorized to update this user"
     * }
     * 
     * @response 403 {
     *   "message": "Only admins can change user roles"
     * }
     * 
     * @response 422 {
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email has already been taken."],
     *     "password": ["The password must be at least 8 characters."]
     *   }
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user);
        try {       
            // Users can only update their own profile, admins can update any user
            if ($user->id !== $request->user()->id && !$request->user()->isAdmin()) {
                Log::warning('Unauthorized access attempt to update user profile', [
                    'requesting_user_id' => $request->user()->id,
                    'requesting_user_email' => $request->user()->email,
                    'target_user_id' => $user->id,
                    'target_user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Unauthorized to update this user'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'password' => 'sometimes|string|min:8',
                'role' => 'sometimes|in:admin,client',
            ]);

            if ($validator->fails()) {
                Log::warning('User profile update validation failed', [
                    'requesting_user_id' => $request->user()->id,
                    'target_user_id' => $user->id,
                    'validation_errors' => $validator->errors()->toArray(),
                    'request_data' => $request->except(['password']),
                    'ip_address' => $request->ip()
                ]);
                
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Only admins can change roles
            if ($request->has('role') && !$request->user()->isAdmin()) {
                Log::warning('Non-admin attempt to change user role', [
                    'requesting_user_id' => $request->user()->id,
                    'requesting_user_email' => $request->user()->email,
                    'target_user_id' => $user->id,
                    'requested_role' => $request->role,
                    'ip_address' => $request->ip()
                ]);
                
                return response()->json([
                    'message' => 'Only admins can change user roles'
                ], 403);
            }

            $updateData = $request->only(['name', 'email']);
            $originalData = $user->only(['name', 'email', 'role']);
            
            // Hash password if provided
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
                Log::info('User password updated', [
                    'user_id' => $user->id,
                    'updated_by' => $request->user()->id,
                    'ip_address' => $request->ip()
                ]);
            }

            // Only admins can update roles
            if ($request->has('role') && $request->user()->isAdmin()) {
                $updateData['role'] = $request->role;
                Log::info('User role changed by admin', [
                    'target_user_id' => $user->id,
                    'target_user_email' => $user->email,
                    'old_role' => $user->role,
                    'new_role' => $request->role,
                    'admin_id' => $request->user()->id,
                    'admin_email' => $request->user()->email,
                    'ip_address' => $request->ip()
                ]);
            }

            $user->update($updateData);
            $updatedUser = $user->fresh();

            Log::info('User profile updated successfully', [
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'updated_by' => $request->user()->id,
                'updated_by_email' => $request->user()->email,
                'changes' => array_diff_assoc($updatedUser->only(['name', 'email', 'role']), $originalData),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'message' => 'User updated successfully',
                'data' => $updatedUser
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating user profile', [
                'requesting_user_id' => $request->user()->id,
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password']),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to update user profile'
            ], 500);
        }
    }

    /**
     * Remove the specified user (admin only)
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @urlParam user integer required The ID of the user to delete. Example: 1
     * 
     * @response {
     *   "message": "User deleted successfully"
     * }
     * 
     * @response 403 {
     *   "message": "Only admins can delete users"
     * }
     * 
     * @response 400 {
     *   "message": "Cannot delete your own account"
     * }
     * 
     * @response 400 {
     *   "message": "Cannot delete user with existing bookings or events"
     * }
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user);
        try {
            // Only admins can delete users
            if (!$request->user()->isAdmin()) {
                Log::warning('Non-admin attempt to delete user', [
                    'requesting_user_id' => $request->user()->id,
                    'requesting_user_email' => $request->user()->email,
                    'target_user_id' => $user->id,
                    'target_user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Only admins can delete users'
                ], 403);
            }

            // Prevent admin from deleting themselves
            if ($user->id === $request->user()->id) {
                Log::warning('Admin attempt to delete own account', [
                    'admin_id' => $request->user()->id,
                    'admin_email' => $request->user()->email,
                    'ip_address' => $request->ip()
                ]);
                
                return response()->json([
                    'message' => 'Cannot delete your own account'
                ], 400);
            }

            // Check if user has any bookings or events
            if ($user->bookings()->exists() || $user->events()->exists()) {
                $bookingCount = $user->bookings()->count();
                $eventCount = $user->events()->count();
                
                Log::warning('Attempt to delete user with existing data', [
                    'admin_id' => $request->user()->id,
                    'admin_email' => $request->user()->email,
                    'target_user_id' => $user->id,
                    'target_user_email' => $user->email,
                    'booking_count' => $bookingCount,
                    'event_count' => $eventCount,
                    'ip_address' => $request->ip()
                ]);
                
                return response()->json([
                    'message' => 'Cannot delete user with existing bookings or events'
                ], 400);
            }

            $userData = $user->only(['id', 'name', 'email', 'role']);
            $user->delete();

            Log::info('User deleted successfully', [
                'admin_id' => $request->user()->id,
                'admin_email' => $request->user()->email,
                'deleted_user' => $userData,
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'admin_id' => $request->user()->id,
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to delete user'
            ], 500);
        }
    }

    /**
     * Get user's bookings
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @urlParam user integer required The ID of the user. Example: 1
     * @queryParam per_page integer Number of results per page. Example: 15
     * 
     * @response {
     *   "data": {
     *     "data": [
     *       {
     *         "id": 1,
     *         "quantity": 2,
     *         "event": {
     *           "id": 1,
     *           "name": "Summer Concert",
     *           "date": "2024-06-15T19:00:00.000000Z"
     *         },
     *         "ticket": {
     *           "id": 1,
     *           "type": "VIP",
     *           "price": "150.00"
     *         }
     *       }
     *     ],
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 1
     *   }
     * }
     * 
     * @response 403 {
     *   "message": "Unauthorized to view this user's bookings"
     * }
     */
    public function bookings(Request $request, User $user): JsonResponse
    {
        try {
            // Users can only view their own bookings, admins can view any user's bookings
            if ($user->id !== $request->user()->id && !$request->user()->isAdmin()) {
                Log::warning('Unauthorized access attempt to view user bookings', [
                    'requesting_user_id' => $request->user()->id,
                    'requesting_user_email' => $request->user()->email,
                    'target_user_id' => $user->id,
                    'target_user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Unauthorized to view this user\'s bookings'
                ], 403);
            }

            $bookings = $user->bookings()
                ->with(['event', 'ticket'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            Log::info('User bookings viewed', [
                'viewer_id' => $request->user()->id,
                'viewer_email' => $request->user()->email,
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'total_bookings' => $bookings->total(),
                'is_admin_view' => $request->user()->isAdmin(),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'data' => $bookings
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing user bookings', [
                'requesting_user_id' => $request->user()->id,
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to view user bookings'
            ], 500);
        }
    }

    /**
     * Get user's events (for admin users)
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @urlParam user integer required The ID of the user. Example: 1
     * @queryParam per_page integer Number of results per page. Example: 15
     * 
     * @response {
     *   "data": {
     *     "data": [
     *       {
     *         "id": 1,
     *         "name": "Summer Concert",
     *         "description": "A great summer concert",
     *         "date": "2024-06-15T19:00:00.000000Z",
     *         "tickets": [
     *           {
     *             "id": 1,
     *             "type": "VIP",
     *             "price": "150.00",
     *             "quantity": 100
     *           }
     *         ],
     *         "bookings": [
     *           {
     *             "id": 1,
     *             "quantity": 2,
     *             "user_id": 2
     *           }
     *         ]
     *       }
     *     ],
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 1
     *   }
     * }
     * 
     * @response 403 {
     *   "message": "Only admins can view user's events"
     * }
     */
    public function events(Request $request, User $user): JsonResponse
    {
        try {
            // Only admins can view user's events
            if (!$request->user()->isAdmin()) {
                Log::warning('Non-admin attempt to view user events', [
                    'requesting_user_id' => $request->user()->id,
                    'requesting_user_email' => $request->user()->email,
                    'target_user_id' => $user->id,
                    'target_user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Only admins can view user\'s events'
                ], 403);
            }

            $events = $user->events()
                ->with(['tickets', 'bookings'])
                ->orderBy('date', 'desc')
                ->paginate($request->get('per_page', 15));

            Log::info('User events viewed by admin', [
                'admin_id' => $request->user()->id,
                'admin_email' => $request->user()->email,
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'total_events' => $events->total(),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'data' => $events
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing user events', [
                'admin_id' => $request->user()->id,
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to view user events'
            ], 500);
        }
    }

    /**
     * Get user statistics (admin only)
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @urlParam user integer required The ID of the user. Example: 1
     * 
     * @response {
     *   "data": {
     *     "total_bookings": 5,
     *     "total_events_created": 2,
     *     "total_tickets_booked": 10,
     *     "upcoming_bookings": 3,
     *     "past_bookings": 2
     *   }
     * }
     * 
     * @response 403 {
     *   "message": "Only admins can view user statistics"
     * }
     */
    public function statistics(Request $request, User $user): JsonResponse
    {
        try {
            // Only admins can view user statistics
            if (!$request->user()->isAdmin()) {
                Log::warning('Non-admin attempt to view user statistics', [
                    'requesting_user_id' => $request->user()->id,
                    'requesting_user_email' => $request->user()->email,
                    'target_user_id' => $user->id,
                    'target_user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Only admins can view user statistics'
                ], 403);
            }

            $statistics = [
                'total_bookings' => $user->bookings()->count(),
                'total_events_created' => $user->events()->count(),
                'total_tickets_booked' => $user->bookings()->sum('quantity'),
                'upcoming_bookings' => $user->bookings()
                    ->whereHas('event', function ($query) {
                        $query->where('date', '>', now());
                    })->count(),
                'past_bookings' => $user->bookings()
                    ->whereHas('event', function ($query) {
                        $query->where('date', '<', now());
                    })->count(),
            ];

            Log::info('User statistics viewed by admin', [
                'admin_id' => $request->user()->id,
                'admin_email' => $request->user()->email,
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'statistics' => $statistics,
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing user statistics', [
                'admin_id' => $request->user()->id,
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to view user statistics'
            ], 500);
        }
    }

    /**
     * Change user password
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @bodyParam current_password string required The user's current password. Example: oldpassword123
     * @bodyParam new_password string required The new password (min 8 characters). Example: newpassword123
     * @bodyParam new_password_confirmation string required Confirmation of the new password. Example: newpassword123
     * 
     * @response {
     *   "message": "Password changed successfully"
     * }
     * 
     * @response 400 {
     *   "message": "Current password is incorrect"
     * }
     * 
     * @response 422 {
     *   "message": "The new password confirmation does not match."
     * }
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
                'new_password_confirmation' => 'required|same:new_password',
            ]);

            $user = $request->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                Log::warning('Password change failed - incorrect current password', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            Log::info('User password changed successfully', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error changing user password', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to change password'
            ], 500);
        }
    }

    /**
     * Get current user's profile
     * 
     * @authenticated
     * 
     * @group User Management
     * 
     * @response {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "role": "client",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z",
     *     "bookings": [
     *       {
     *         "id": 1,
     *         "quantity": 2,
     *         "event": {
     *           "id": 1,
     *           "name": "Summer Concert",
     *           "date": "2024-06-15T19:00:00.000000Z"
     *         }
     *       }
     *     ],
     *     "events": []
     *   }
     * }
     * 
     * @response 500 {
     *   "message": "Failed to load user profile"
     * }
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load(['bookings.event', 'events']);

            Log::info('User profile accessed', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'booking_count' => $user->bookings->count(),
                'event_count' => $user->events->count(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error accessing user profile', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to load user profile'
            ], 500);
        }
    }
} 