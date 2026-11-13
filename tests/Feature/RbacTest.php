<?php

namespace Tests\Feature;

use App\Booking;
use App\Category;
use App\Destinations;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Laboratory Activity 2 - RBAC (Admin / Customer) feature tests.
 */
class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function customer(): User
    {
        return User::factory()->create(); // factory default role = customer
    }

    // ------------------------------------------------------------------
    // Unauthenticated visitors
    // ------------------------------------------------------------------

    public function test_guests_are_redirected_to_login_for_protected_pages(): void
    {
        foreach (['/admin/dashboard', '/admin/bookings', '/users', '/destinations', '/bookings', '/wishlist', '/home'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    // ------------------------------------------------------------------
    // Role assignment
    // ------------------------------------------------------------------

    public function test_registration_always_creates_a_customer_even_if_role_is_submitted(): void
    {
        $this->post('/register', [
            'name' => 'New Person',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_customer_cannot_escalate_role_through_profile_update_or_mass_assignment(): void
    {
        $customer = $this->customer();

        // Profile routes are admin-only, so the request is refused outright.
        $this->actingAs($customer)->put('/users/profile', [
            'name' => 'Hacker', 'about' => 'x', 'role' => 'admin',
        ])->assertForbidden();

        // And 'role' is not mass assignable on the model.
        $customer->update(['role' => 'admin']);
        $this->assertSame('customer', $customer->fresh()->role);
    }

    public function test_customer_cannot_make_themselves_admin(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->post("/users/{$customer->id}/make-admin")->assertForbidden();

        $this->assertFalse($customer->fresh()->isAdmin());
    }

    public function test_admin_can_change_roles_but_not_demote_themselves(): void
    {
        $admin = $this->admin();
        $other = $this->admin();

        $this->actingAs($admin)->post("/users/{$other->id}/make-customer")->assertRedirect('/users');
        $this->assertSame('customer', $other->fresh()->role);

        $this->actingAs($admin)->post("/users/{$admin->id}/make-customer")->assertForbidden();
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    // ------------------------------------------------------------------
    // Admin access (screenshot 01)
    // ------------------------------------------------------------------

    public function test_admin_can_access_dashboard_users_and_booking_management(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/users')->assertOk();
        $this->actingAs($admin)->get('/admin/bookings')->assertOk();
    }

    public function test_home_redirects_each_role_to_the_right_place(): void
    {
        $this->actingAs($this->admin())->get('/home')->assertRedirect('/admin/dashboard');
        $this->actingAs($this->customer())->get('/home')->assertRedirect('/');
    }

    // ------------------------------------------------------------------
    // Customer access (screenshot 02)
    // ------------------------------------------------------------------

    public function test_customer_can_access_customer_pages(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->get('/wishlist')->assertOk();
        $this->actingAs($customer)->get('/bookings')->assertOk();
    }

    // ------------------------------------------------------------------
    // Role denied (screenshot 03)
    // ------------------------------------------------------------------

    public function test_customer_is_forbidden_from_every_admin_url(): void
    {
        $customer = $this->customer();

        foreach ([
            '/admin/dashboard', '/admin/bookings', '/users', '/users/profile',
            '/destinations', '/destinations/create', '/trashed-destinations',
            '/categories', '/tags', '/blog', '/send-email',
        ] as $url) {
            $this->actingAs($customer)->get($url)->assertForbidden();
        }
    }

    // ------------------------------------------------------------------
    // Ownership protection (screenshot 04)
    // ------------------------------------------------------------------

    public function test_customer_can_view_own_booking_but_not_someone_elses(): void
    {
        $owner = $this->customer();
        $stranger = $this->customer();
        $booking = Booking::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->get("/bookings/{$booking->id}")->assertOk();
        $this->actingAs($stranger)->get("/bookings/{$booking->id}")->assertForbidden();
    }

    public function test_booking_list_only_contains_the_customers_own_bookings(): void
    {
        $owner = $this->customer();
        $stranger = $this->customer();
        $mine = Booking::factory()->create(['user_id' => $owner->id, 'notes' => 'MY-OWN-NOTE']);
        Booking::factory()->create(['user_id' => $stranger->id, 'notes' => 'SOMEONE-ELSES-NOTE']);

        $response = $this->actingAs($owner)->get('/bookings');

        $response->assertOk();
        $response->assertViewHas('bookings', fn ($bookings) => $bookings->count() === 1
            && $bookings->first()->id === $mine->id);
    }

    public function test_customer_cannot_cancel_or_manage_someone_elses_booking(): void
    {
        $owner = $this->customer();
        $stranger = $this->customer();
        $booking = Booking::factory()->create(['user_id' => $owner->id, 'status' => 'pending']);

        $this->actingAs($stranger)->patch("/bookings/{$booking->id}/cancel")->assertForbidden();
        $this->assertSame('pending', $booking->fresh()->status);

        // Admin-only booking management is closed to customers entirely.
        $this->actingAs($stranger)->patch("/admin/bookings/{$booking->id}", ['status' => 'confirmed'])->assertForbidden();
        $this->actingAs($stranger)->delete("/admin/bookings/{$booking->id}")->assertForbidden();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'pending']);
    }

    public function test_customer_can_cancel_own_pending_booking(): void
    {
        $owner = $this->customer();
        $booking = Booking::factory()->create(['user_id' => $owner->id, 'status' => 'pending']);

        $this->actingAs($owner)->patch("/bookings/{$booking->id}/cancel")->assertRedirect('/bookings');

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_booking_owner_status_and_price_are_decided_by_the_server(): void
    {
        $customer = $this->customer();
        $other = $this->customer();
        $destination = Destinations::factory()->create(['pricing' => '₱1000']);

        $this->actingAs($customer)->post('/bookings', [
            'destination_id' => $destination->id,
            'travel_date' => now()->addWeek()->toDateString(),
            'guests' => 3,
            // attempted tampering - must all be ignored
            'user_id' => $other->id,
            'status' => 'confirmed',
            'total_price' => 1,
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame($customer->id, $booking->user_id);
        $this->assertSame('pending', $booking->status);
        $this->assertEquals(3000, $booking->total_price);
    }

    public function test_wishlist_actions_only_affect_the_logged_in_users_wishlist(): void
    {
        $alice = $this->customer();
        $bob = $this->customer();
        $destination = Destinations::factory()->create();

        $this->actingAs($alice)->post("/wishlist/{$destination->id}")->assertRedirect();
        $this->assertDatabaseHas('wishlists', ['user_id' => $alice->id, 'destination_id' => $destination->id]);

        // Bob "removing" the same destination cannot touch Alice's row.
        $this->actingAs($bob)->delete("/wishlist/{$destination->id}")->assertRedirect();
        $this->assertDatabaseHas('wishlists', ['user_id' => $alice->id, 'destination_id' => $destination->id]);

        // Bob's wishlist page never lists Alice's saved destination.
        $this->actingAs($bob)->get('/wishlist')->assertOk()
            ->assertViewHas('wishlisted', fn ($list) => $list->count() === 0);
    }

    public function test_admin_can_view_and_manage_all_bookings(): void
    {
        $admin = $this->admin();
        $booking = Booking::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->get("/bookings/{$booking->id}")->assertOk();

        $this->actingAs($admin)->patch("/admin/bookings/{$booking->id}", ['status' => 'confirmed'])->assertRedirect();
        $this->assertSame('confirmed', $booking->fresh()->status);

        $this->actingAs($admin)->patch("/admin/bookings/{$booking->id}", ['status' => 'hacked'])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)->delete("/admin/bookings/{$booking->id}")->assertRedirect();
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    // ------------------------------------------------------------------
    // Restricted actions denied (screenshot 05)
    // ------------------------------------------------------------------

    public function test_customer_cannot_create_update_or_delete_destinations(): void
    {
        $customer = $this->customer();
        $category = Category::factory()->create();
        $destination = Destinations::factory()->create();

        $this->actingAs($customer)->post('/destinations', [
            'title' => 'Hacked', 'description' => 'x', 'content' => 'x',
            'image' => UploadedFile::fake()->image('a.jpg'), 'pricing' => '1',
            'category' => $category->id,
        ])->assertForbidden();
        $this->assertDatabaseMissing('destinations', ['title' => 'Hacked']);

        $this->actingAs($customer)->put("/destinations/{$destination->id}", [
            'title' => 'Hacked', 'description' => 'x', 'content' => 'x', 'pricing' => '1',
            'category' => $category->id,
        ])->assertForbidden();
        $this->assertDatabaseMissing('destinations', ['title' => 'Hacked']);

        $this->actingAs($customer)->delete("/destinations/{$destination->id}")->assertForbidden();
        $this->assertNotSoftDeleted('destinations', ['id' => $destination->id]);
    }
}
