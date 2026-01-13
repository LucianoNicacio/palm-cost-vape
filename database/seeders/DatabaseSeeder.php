<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user if doesn't exist
        if (!User::where('email', 'admin@palmcoastvape.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@palmcoastvape.com',
                'password' => bcrypt('password123'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
            $this->command->info('Created admin user: admin@palmcoastvape.com / password123');
        }

        // Run seeders in order
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            ReservationSeeder::class, // This also creates customers
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed!');
        $this->command->info('📧 Admin login: admin@palmcoastvape.com / password123');
    }
}
