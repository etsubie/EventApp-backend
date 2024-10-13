<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $hostRole = Role::create(['name' => 'host']);
        $attendeeRole = Role::create(['name' => 'attendee']);

        // Create permissions
        Permission::create( ['name' => 'manage users']);
        Permission::create(['name' => 'create events']);
        Permission::create(['name' => 'manage events']);
        Permission::create(['name' => 'approve events']);
        Permission::create(['name' => 'view events']);
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'book events']);
        Permission::create(['name' => 'view booked events']);

        
        // Assign permissions to roles
        $adminRole->givePermissionTo(['manage users','approve events','view users', 'manage events', 'view events', 'view booked events']);
        $hostRole->givePermissionTo(['create events', 'manage events', 'view events', 'view booked events']);
        $attendeeRole->givePermissionTo(['view events', 'book events',]);
    }
}
