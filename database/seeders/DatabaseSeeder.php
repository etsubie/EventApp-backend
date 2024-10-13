<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $user = User::factory()->create([
            'name' => 'Etsub',
            'email' => 'test@example.com',
            'password' => bcrypt('12345'), 
        ]);

        // Assign the 'admin' role to the user
        $user->assignRole('admin');
    }
}
