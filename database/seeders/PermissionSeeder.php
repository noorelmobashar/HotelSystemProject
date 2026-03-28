<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'manage managers']);
        Permission::firstOrCreate(['name' => 'manage receptionists']);
        Permission::firstOrCreate(['name' => 'manage clients']);
        Permission::firstOrCreate(['name' => 'manage floors']);
        Permission::firstOrCreate(['name' => 'manage rooms']);
        Permission::firstOrCreate(['name' => 'make reservation']);
        Permission::firstOrCreate(['name' => 'approve clients']);


        $admin = Role::findByName('admin');
        $manager = Role::findByName('manager');
        $receptionist = Role::findByName('receptionist');
        $client = Role::findByName('client');

        $admin->syncPermissions(Permission::all());

        $manager->syncPermissions([
            'manage receptionists',
            'manage clients',
            'manage floors',
            'manage rooms',
        ]);


        $receptionist->syncPermissions([
            'approve clients',
            'make reservation',
        ]);

        $client->syncPermissions([
            'make reservation',
        ]);
    }
}
