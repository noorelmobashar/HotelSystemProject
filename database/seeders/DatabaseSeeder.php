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

        $user = User::updateOrCreate([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Admin',
            'gender' => 'male',
            'password' => bcrypt('123456'),
        ]);

        $user->assignRole('admin');

    }
}
