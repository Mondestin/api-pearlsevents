<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->admin = User::factory()->admin()->create();
        $this->client = User::factory()->client()->create();
    }

    /** @test */
    public function admin_can_create_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'client'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created successfully',
                'data' => [
                    'name' => 'New User',
                    'email' => 'newuser@example.com',
                    'role' => 'client'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'client'
        ]);
    }

    /** @test */
    public function client_cannot_create_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'client'
        ];

        $response = $this->actingAs($this->client)
            ->postJson('/api/users', $userData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Only admins can create users'
            ]);
    }

    /** @test */
    public function admin_can_create_user_without_role()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'role' => 'client' // Should default to client
                ]
            ]);
    }

    /** @test */
    public function admin_can_create_admin_user()
    {
        $userData = [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'role' => 'admin'
                ]
            ]);
    }

    /** @test */
    public function user_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function user_creation_validates_email_uniqueness()
    {
        // Create a user first
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com', // Duplicate email
            'password' => 'password123'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function user_creation_validates_password_length()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => '123' // Too short
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function admin_can_list_all_users()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'role',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function client_cannot_list_all_users()
    {
        $response = $this->actingAs($this->client)
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_view_own_profile()
    {
        $response = $this->actingAs($this->client)
            ->getJson('/api/users/' . $this->client->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                    'email' => $this->client->email
                ]
            ]);
    }

    /** @test */
    public function user_cannot_view_other_user_profile()
    {
        $response = $this->actingAs($this->client)
            ->getJson('/api/users/' . $this->admin->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_any_user_profile()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/users/' . $this->client->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_update_own_profile()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->actingAs($this->client)
            ->putJson('/api/users/' . $this->client->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com'
                ]
            ]);
    }

    /** @test */
    public function user_cannot_update_other_user_profile()
    {
        $updateData = [
            'name' => 'Updated Name'
        ];

        $response = $this->actingAs($this->client)
            ->putJson('/api/users/' . $this->admin->id, $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_any_user_profile()
    {
        $updateData = [
            'name' => 'Admin Updated Name'
        ];

        $response = $this->actingAs($this->admin)
            ->putJson('/api/users/' . $this->client->id, $updateData);

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_change_user_role()
    {
        $updateData = [
            'role' => 'admin'
        ];

        $response = $this->actingAs($this->admin)
            ->putJson('/api/users/' . $this->client->id, $updateData);

        $response->assertStatus(200);
        
        $this->client->refresh();
        $this->assertEquals('admin', $this->client->role);
    }

    /** @test */
    public function client_cannot_change_user_role()
    {
        $updateData = [
            'role' => 'admin'
        ];

        $response = $this->actingAs($this->client)
            ->putJson('/api/users/' . $this->client->id, $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_change_own_password()
    {
        $passwordData = [
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAs($this->client)
            ->postJson('/api/change-password', $passwordData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password changed successfully'
            ]);
    }

    /** @test */
    public function user_cannot_change_password_with_wrong_current_password()
    {
        $passwordData = [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAs($this->client)
            ->postJson('/api/change-password', $passwordData);

        $response->assertStatus(400);
    }

    /** @test */
    public function user_can_view_own_bookings()
    {
        // Create a booking for the client
        $event = Event::factory()->create(['user_id' => $this->admin->id]);
        $ticket = Ticket::factory()->create(['event_id' => $event->id]);
        Booking::factory()->create([
            'user_id' => $this->client->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id
        ]);

        $response = $this->actingAs($this->client)
            ->getJson('/api/users/' . $this->client->id . '/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'quantity',
                            'event',
                            'ticket'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function admin_can_view_user_statistics()
    {
        // Create some bookings for the client
        $event = Event::factory()->create(['user_id' => $this->admin->id]);
        $ticket = Ticket::factory()->create(['event_id' => $event->id]);
        Booking::factory()->count(3)->create([
            'user_id' => $this->client->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/users/' . $this->client->id . '/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_bookings',
                    'total_events_created',
                    'total_tickets_booked',
                    'upcoming_bookings',
                    'past_bookings'
                ]
            ]);
    }

    /** @test */
    public function admin_can_delete_user_without_bookings()
    {
        $newUser = User::factory()->client()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson('/api/users/' . $newUser->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully'
            ]);
    }

    /** @test */
    public function admin_cannot_delete_user_with_bookings()
    {
        // Create a booking for the client
        $event = Event::factory()->create(['user_id' => $this->admin->id]);
        $ticket = Ticket::factory()->create(['event_id' => $event->id]);
        Booking::factory()->create([
            'user_id' => $this->client->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson('/api/users/' . $this->client->id);

        $response->assertStatus(400);
    }

    /** @test */
    public function admin_cannot_delete_own_account()
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson('/api/users/' . $this->admin->id);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cannot delete your own account'
            ]);
    }
} 