<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets for an event
     */
    public function index(Request $request): JsonResponse
    {
        $event = Event::findOrFail($request->event);
        // Check if the tickets table exists
        if (!Schema::hasTable('tickets')) {
            return response()->json([
                'message' => 'Database tables not set up yet. Please run migrations.',
                'data' => []
            ]);
        }

        $tickets = $event->tickets()->with('bookings')->get();

        return response()->json([
            'data' => $tickets
        ]);
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request): JsonResponse
    {
        $event = Event::findOrFail($request->event);
        // Only admins can add tickets
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can add tickets'
            ], 403);
        }

        $request->validate([
            'type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $ticket = $event->tickets()->create([
            'type' => $request->type,
            'price' => $request->price,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'data' => $ticket
        ], 201);
    }

    /**
     * Display the specified ticket
     */
    public function show(Request $request): JsonResponse
    {
        $ticket = Ticket::findOrFail($request->ticket);
        return response()->json([
            'data' => $ticket->load(['event', 'bookings.user'])
        ]);
    }

    /**
     * Update the specified ticket
     */
    public function update(Request $request): JsonResponse
    {
        $ticket = Ticket::findOrFail($request->ticket);
        // Only admins can update tickets
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can update tickets'
            ], 403);
        }

        $request->validate([
            'type' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:1',
        ]);

        $ticket->update($request->only(['type', 'price', 'quantity']));

        return response()->json([
            'message' => 'Ticket updated successfully',
            'data' => $ticket
        ]);
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(Request $request): JsonResponse
    {
        $ticket = Ticket::findOrFail($request->ticket);
        // Only admins can delete tickets
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can delete tickets'
            ], 403);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully'
        ]);
    }
} 