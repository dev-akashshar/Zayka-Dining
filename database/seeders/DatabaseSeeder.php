<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use Database\Factories\AttendanceFactory;
use Database\Factories\BlogFactory;
use Database\Factories\MenuItemFactory;
use Database\Factories\TableFactory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Roles ─────────────────────────────────────────────────────────
        foreach (['super_admin', 'manager', 'staff', 'customer'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // ── 2. Super admin ────────────────────────────────────────────────────
        User::factory()->superAdmin()->create();

        // ── 3. Customers ─────────────────────────────────────────────────────
        // One fixed test customer + 9 random Indian-named customers
        $testCustomer = User::factory()->testCustomer()->create();

        $customers = User::factory()
            ->count(9)
            ->customer()
            ->create();

        $customers->prepend($testCustomer); // index 0 = test customer

        // ── 4. Restaurants (factory downloads Picsum cover photos) ───────────
        $restaurants = Restaurant::factory()
            ->count(10)
            ->create();

        $this->command->info('✅ Restaurants created and cover photos downloaded.');

        // ── 5. Per-restaurant fixtures ────────────────────────────────────────
        foreach ($restaurants as $idx => $restaurant) {
            $num = $idx + 1;

            // Manager
            User::factory()
                ->manager($restaurant->id)
                ->create([
                    'name'  => "Manager {$restaurant->name}",
                    'email' => "manager{$num}@example.com",
                ]);

            // Staff
            $staff = User::factory()
                ->staff($restaurant->id)
                ->create([
                    'name'  => "Staff {$restaurant->name}",
                    'email' => "staff{$num}@example.com",
                ]);

            // 6 seating tables
            $tables = TableFactory::defaultSet($restaurant);

            // 10 menu items (4 starters / 4 mains / 1 dessert / 1 beverage)
            MenuItemFactory::defaultSet($restaurant);

            // 2 curated blogs
            BlogFactory::defaultSet($restaurant, $idx);

            // 5 bookings cycling through statuses and customers
            $statuses   = ['confirmed', 'pending', 'cancelled', 'rejected', 'completed'];
            $payData    = [
                'confirmed'  => ['paid_full',    'full',    4999.00],
                'completed'  => ['paid_full',    'full',    4999.00],
                'pending'    => ['paid_advance', 'advance', 1250.00],
                'cancelled'  => ['unpaid',       'none',       0.00],
                'rejected'   => ['unpaid',       'none',       0.00],
            ];

            for ($b = 0; $b < 5; $b++) {
                $status                            = $statuses[($idx + $b) % 5];
                [$payStatus, $payType, $amount]    = $payData[$status];
                $table                             = $tables[$b % count($tables)];
                $customer                          = $customers[($idx + $b) % $customers->count()];

                $restaurant->bookings()->create([
                    'user_id'        => $customer->id,
                    'table_id'       => $table->id,
                    'guest_count'    => min($table->capacity, 2 + ($b % 4)),
                    'booking_date'   => now()->addDays(($idx + $b) % 7)->subDays(2)->toDateString(),
                    'booking_time'   => sprintf('%02d:00:00', 12 + ($b * 2) % 11),
                    'status'         => $status,
                    'payment_status' => $payStatus,
                    'payment_type'   => $payType,
                    'payment_amount' => $amount,
                    'notes'          => 'Aesthetic royal seating preferred.',
                ]);
            }

            // 2 attendance records per staff member
            $restaurant->attendances()->create([
                'user_id'   => $staff->id,
                'check_in'  => now()->subDay()->setHour(9)->setMinute(30),
                'check_out' => now()->subDay()->setHour(18)->setMinute(15),
                'notes'     => 'Managed kitchen preparation shift.',
            ]);

            $restaurant->attendances()->create([
                'user_id'  => $staff->id,
                'check_in' => now()->setHour(9)->setMinute(15),
                'notes'    => 'Active check-in morning duty roster.',
            ]);
        }

        // ── Summary ───────────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('✅ Database seeded successfully!');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Restaurants',  10],
                ['Managers',     10],
                ['Staff',        10],
                ['Customers',    10],
                ['Tables',       60],
                ['Menu Items',  100],
                ['Blogs',        20],
                ['Bookings',     50],
                ['Attendance',   20],
            ]
        );
        $this->command->newLine();
        $this->command->line('  <fg=yellow>admin@gmail.com</> / password  →  Super Admin');
        $this->command->line('  <fg=yellow>manager1@example.com</> / password  →  Manager');
        $this->command->line('  <fg=yellow>customer@example.com</> / password  →  Customer');
    }
}
