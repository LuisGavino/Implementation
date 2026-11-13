<?php

namespace Database\Seeders;

use App\Booking;
use App\Destinations;
use App\User;
use Illuminate\Database\Seeder;

/**
 * Demo bookings for the two demo customers, used to test RBAC ownership rules
 * (customer1 must NOT be able to open customer2's booking and vice-versa).
 */
class BookingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $destination = Destinations::first();

        if (! $destination) {
            return;
        }

        foreach (['customer1@example.com', 'customer2@example.com'] as $email) {
            $user = User::where('email', $email)->first();

            if ($user && ! $user->bookings()->exists()) {
                $user->bookings()->create([
                    'destination_id' => $destination->id,
                    'travel_date' => now()->addMonth()->toDateString(),
                    'guests' => 2,
                    'total_price' => $destination->price * 2,
                    'status' => 'pending',
                    'notes' => 'Demo booking for '.$email,
                ]);
            }
        }
    }
}
