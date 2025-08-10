<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of events
     */
    public function index(Request $request): JsonResponse
    {
        // Check if the events table exists
        if (!Schema::hasTable('events')) {
            return response()->json([
                'message' => 'Database tables not set up yet. Please run migrations.',
                'data' => []
            ]);
        }

        $query = Event::with(['user', 'tickets']);

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by upcoming/past events
        if ($request->has('type')) {
            if ($request->type === 'upcoming') {
                $query->upcoming();
            } elseif ($request->type === 'past') {
                $query->past();
            }
        }

        $events = $query->get();

        return response()->json([
            'data' => $events
        ]);
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request): JsonResponse
    {
        // Only admins can create events
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can create events'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'date' => 'required|date|after:now',
            'pictures' => 'nullable|array',
            'pictures.*' => 'nullable|string|url',
            'picture_files' => 'nullable|array',
            'picture_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $pictures = $request->pictures ?? [];

        // Handle file uploads
        if ($request->hasFile('picture_files')) {
            foreach ($request->file('picture_files') as $file) {
                if ($file->isValid()) {
                    $filename = 'events/' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public', $filename);
                    $pictures[] = Storage::url($filename);
                }
            }
        }

        $event = Event::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'date' => $request->date,
            'pictures' => $pictures,
        ]);

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event->load(['user', 'tickets'])
        ], 201);
    }

    /**
     * Display the specified event
     */
    public function show(Request $request): JsonResponse
    {
        $event = Event::findOrFail($request->event);
        return response()->json([
            'data' => $event->load(['user', 'tickets', 'bookings.user'])
        ]);
    }

    /**
     * Update the specified event
     */
    public function update(Request $request): JsonResponse
    {
        $event = Event::findOrFail($request->event);
        // Only admins can update events
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can update events'
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date|after:now',
            'pictures' => 'nullable|array',
            'pictures.*' => 'nullable|string|url',
            'picture_files' => 'nullable|array',
            'picture_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $updateData = $request->only(['name', 'description', 'location', 'date']);

        // Handle file uploads for updates
        if ($request->hasFile('picture_files')) {
            $pictures = $event->pictures ?? [];
            
            foreach ($request->file('picture_files') as $file) {
                if ($file->isValid()) {
                    $filename = 'events/' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public', $filename);
                    $pictures[] = Storage::url($filename);
                }
            }
            
            $updateData['pictures'] = $pictures;
        } elseif ($request->has('pictures')) {
            $updateData['pictures'] = $request->pictures;
        }

        $event->update($updateData);

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => $event->load(['user', 'tickets'])
        ]);
    }

    /**
     * Remove the specified event
     */
    public function destroy(Request $request): JsonResponse
    {
        $event = Event::findOrFail($request->event);
        // Only admins can delete events
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can delete events'
            ], 403);
        }

        // Delete associated picture files from storage
        if ($event->pictures) {
            foreach ($event->pictures as $pictureUrl) {
                $this->deletePictureFromStorage($pictureUrl);
            }
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }

    /**
     * Search events
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $query = Event::with(['user', 'tickets'])
            ->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('description', 'like', "%{$request->q}%")
                  ->orWhere('location', 'like', "%{$request->q}%");
            });

        $events = $query->get();

        return response()->json([
            'data' => $events
        ]);
    }

    /**
     * Upload pictures for an event
     */
    public function uploadPictures(Request $request): JsonResponse
    {
        $event = Event::findOrFail($request->event);
        
        // Only admins can upload pictures
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can upload pictures'
            ], 403);
        }

        $request->validate([
            'pictures' => 'required|array',
            'pictures.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedPictures = [];

        foreach ($request->file('pictures') as $file) {
            if ($file->isValid()) {
                $filename = 'events/' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public', $filename);
                $uploadedPictures[] = Storage::url($filename);
            }
        }

        // Add new pictures to existing ones
        $pictures = $event->pictures ?? [];
        $pictures = array_merge($pictures, $uploadedPictures);
        $event->update(['pictures' => $pictures]);

        return response()->json([
            'message' => 'Pictures uploaded successfully',
            'data' => [
                'uploaded_pictures' => $uploadedPictures,
                'total_pictures' => count($pictures),
                'event' => $event->load(['user', 'tickets'])
            ]
        ]);
    }

    /**
     * Add a picture to an event
     */
    public function addPicture(Request $request): JsonResponse
    {
        $event = Event::findOrFail($request->event);
        
        // Only admins can add pictures
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can add pictures'
            ], 403);
        }

        $request->validate([
            'picture_url' => 'required|string|url',
        ]);

        $event->addPicture($request->picture_url);

        return response()->json([
            'message' => 'Picture added successfully',
            'data' => $event->load(['user', 'tickets'])
        ]);
    }

    /**
     * Remove a picture from an event
     */
    public function removePicture(Request $request): JsonResponse
    {
        $event = Event::findOrFail($request->event);
        
        // Only admins can remove pictures
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can remove pictures'
            ], 403);
        }

        $request->validate([
            'picture_url' => 'required|string|url',
        ]);

        // Delete file from storage if it's a local file
        $this->deletePictureFromStorage($request->picture_url);

        $event->removePicture($request->picture_url);

        return response()->json([
            'message' => 'Picture removed successfully',
            'data' => $event->load(['user', 'tickets'])
        ]);
    }

    /**
     * Delete picture file from storage
     */
    private function deletePictureFromStorage(string $pictureUrl): void
    {
        // Only delete local files, not external URLs
        if (str_starts_with($pictureUrl, '/storage/')) {
            $path = str_replace('/storage/', '', $pictureUrl);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
} 