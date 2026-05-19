<?php

namespace Database\Seeders;

use App\Models\Booking;


use App\Models\Payment;
use App\Models\TermsAndCondition;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Create Spatie roles ───────────────────────────────────────────────
        $roleCustomer   = Role::create(['name' => 'customer']);
        $roleAdmin      = Role::create(['name' => 'admin']);
        $roleSuperAdmin = Role::create(['name' => 'super_admin']);

        // ── Seed users ────────────────────────────────────────────────────────
        $superAdmin = User::create([
            'first_name' => 'Super',
            'last_name'  => 'Admin',
            'email'      => 'superadmin@orangecrush.com',
            'password'   => Hash::make('password'),
            'phone'      => '09000000001',
        ]);
        $superAdmin->assignRole('super_admin');

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name'  => 'User',
            'email'      => 'admin@orangecrush.com',
            'password'   => Hash::make('password'),
            'phone'      => '09000000002',
        ]);
        $admin->assignRole('admin');

        $customer1 = User::create([
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'email'      => 'customer@orangecrush.com',
            'password'   => Hash::make('password'),
            'phone'      => '09123456789',
        ]);
        $customer1->assignRole('customer');

        $customer2 = User::create([
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
            'email'      => 'maria@orangecrush.com',
            'password'   => Hash::make('password'),
            'phone'      => '09987654321',
        ]);
        $customer2->assignRole('customer');



        // ── Vehicles ──────────────────────────────────────────────────────────
        $v1 = Vehicle::create([
            'name'          => 'Toyota Vios',
            'brand'         => 'Toyota',
            'model'         => 'Vios',
            'year'          => 2022,
            'plate_number'  => 'ABC-1234',
            'type'          => 'Sedan',
            'transmission'  => 'Automatic',
            'fuel'          => 'Gasoline',
            'capacity'      => 5,
            'price_per_day' => 1500.00,
            'status'        => 'available',
            'image'         => 'vehicles/toyota_vios.png',
            'description'   => 'Reliable and fuel-efficient sedan perfect for city driving.',
        ]);

        $v2 = Vehicle::create([
            'name'          => 'Honda HR-V',
            'brand'         => 'Honda',
            'model'         => 'HR-V',
            'year'          => 2023,
            'plate_number'  => 'XYZ-5678',
            'type'          => 'SUV',
            'transmission'  => 'Automatic',
            'fuel'          => 'Gasoline',
            'capacity'      => 5,
            'price_per_day' => 2200.00,
            'status'        => 'available',
            'image'         => 'vehicles/honda_hrv.png',
            'description'   => 'Compact SUV with great ground clearance and modern features.',
        ]);

        $v3 = Vehicle::create([
            'name'          => 'Mitsubishi Montero Sport',
            'brand'         => 'Mitsubishi',
            'model'         => 'Montero Sport',
            'year'          => 2023,
            'plate_number'  => 'MNO-9012',
            'type'          => 'SUV',
            'transmission'  => 'Automatic',
            'fuel'          => 'Diesel',
            'capacity'      => 7,
            'price_per_day' => 3500.00,
            'status'        => 'available',
            'image'         => 'vehicles/mitsubishi_montero.png',
            'description'   => '7-seater SUV with powerful diesel engine, great for family trips.',
        ]);

        $v4 = Vehicle::create([
            'name'          => 'Ford Ranger',
            'brand'         => 'Ford',
            'model'         => 'Ranger',
            'year'          => 2022,
            'plate_number'  => 'PQR-3456',
            'type'          => 'Pickup Truck',
            'transmission'  => 'Automatic',
            'fuel'          => 'Diesel',
            'capacity'      => 5,
            'price_per_day' => 3000.00,
            'status'        => 'available',
            'image'         => 'vehicles/ford_ranger.png',
            'description'   => 'Tough pickup truck with large cargo capacity, ideal for adventure.',
        ]);

        Vehicle::create([
            'name'          => 'Toyota Innova',
            'brand'         => 'Toyota',
            'model'         => 'Innova',
            'year'          => 2021,
            'plate_number'  => 'STU-7890',
            'type'          => 'Van',
            'transmission'  => 'Manual',
            'fuel'          => 'Diesel',
            'capacity'      => 8,
            'price_per_day' => 2800.00,
            'status'        => 'available',
            'image'         => 'vehicles/toyota_innova.png',
            'description'   => 'Spacious MPV perfect for group trips and family outings.',
        ]);

        /*
        // ── Sample booking (awaiting verification) ────────────────────────────
        $booking = Booking::create([
            'user_id'                => $customer1->id,
            'vehicle_id'             => $v1->id,
            'first_name'             => 'Juan',
            'last_name'              => 'Dela Cruz',
            'email'                  => 'juan@example.com',
            'phone'                  => '09123456789',
            'drivers_license_number' => 'DL-2024-001',
            'pickup_date'            => now()->addDays(3),
            'return_date'            => now()->addDays(6),
            'total_amount'           => 4500.00,
            'status'                 => 'awaiting_verification',
            'terms_agreed_at'        => now(),
        ]);

        Payment::create([
            'booking_id'      => $booking->id,
            'amount'          => 4500.00,
            'payment_method'  => 'gcash',
            'reference_code'  => 'GC-20240430-001',
            'screenshot_path' => 'payments/sample.jpg',
            'status'          => 'pending',
        ]);
        */

        // ── Terms & Conditions ────────────────────────────────────────────────
        TermsAndCondition::create([
            'content'    => "<h2>OrangeCrush Car Rentals — Terms &amp; Conditions</h2>
<p>By booking a vehicle through OrangeCrush Car Rentals, you agree to the following terms:</p>
<ol>
<li><strong>Valid ID Required:</strong> A valid government-issued ID and driver's license must be presented upon vehicle pickup.</li>
<li><strong>Payment:</strong> Full payment via GCash is required before the booking is confirmed.</li>
<li><strong>Damage Liability:</strong> The renter is responsible for any damage to the vehicle during the rental period.</li>
<li><strong>Fuel Policy:</strong> Vehicles must be returned with the same fuel level as provided.</li>
<li><strong>Late Returns:</strong> Late returns beyond the agreed time will incur additional daily charges.</li>
<li><strong>Cancellation:</strong> Cancellations made 24 hours before pickup are eligible for a partial refund.</li>
<li><strong>Traffic Violations:</strong> The renter is solely responsible for any traffic violations incurred during the rental period.</li>
</ol>",
            'updated_by' => $superAdmin->id,
        ]);
    }
}
