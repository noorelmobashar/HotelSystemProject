<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        $this->call([
            RoleSeeder::class,  
            PermissionSeeder::class,
        ]);

        $admin = User::updateOrCreate([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Admin',
            'gender' => 'male',
            'password' => bcrypt('123456'),
        ]);
        $admin->assignRole('admin');

        $manager = User::updateOrCreate([
            'name' => 'Manager',
            'email' => 'manager@manager.com',
        ], [
            'name' => 'Manager',
            'gender' => 'male',
            'password' => bcrypt('123456'),
        ]);
        $manager->assignRole('manager');

        $managerTwo = User::updateOrCreate([
            'name' => 'Manager Two',
            'email' => 'manager2@manager.com',
        ], [
            'name' => 'Manager Two',
            'gender' => 'male',
            'password' => bcrypt('123456'),
        ]);
        $managerTwo->assignRole('manager');

        $receptionist = User::updateOrCreate([
            'name' => 'Receptionist',
            'email' => 'receptionist@receptionist.com',
        ], [
            'name' => 'Receptionist',
            'gender' => 'female',
            'password' => bcrypt('123456'),
        ]);
        $receptionist->assignRole('receptionist');

        $client = User::updateOrCreate([
            'name' => 'Client',
            'email' => 'client@client.com',
        ], [
            'name' => 'Client',
            'gender' => 'male',
            'password' => bcrypt('123456'),
        ]);
        $client->assignRole('client');

        $this->call([
            FloorSeeder::class,
        ]);

    }
}
