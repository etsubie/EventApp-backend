<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Create a user and assign the 'admin' role
        $user = User::factory()->create([
            'name' => 'ADMIN',
            'email' => 'test@example.com',
            'password' => bcrypt('12345'), 
        ]);
        
        // Assign the 'admin' role to the user
        $user->assignRole('admin');

        $this->call(CategorySeeder::class);
        $this->call(EventSeeder::class); 
    }
}
