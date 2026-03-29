<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\User;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerIds = User::role('manager')->pluck('id')->values();

        if ($managerIds->isEmpty()) {
            return;
        }

        $floors = [
            ['name' => 'Ground Floor', 'number' => '1000'],
            ['name' => 'First Floor', 'number' => '1001'],
            ['name' => 'Second Floor', 'number' => '1002'],
            ['name' => 'Third Floor', 'number' => '1003'],
        ];

        foreach ($floors as $index => $floor) {
            $createdBy = $managerIds[$index % $managerIds->count()];

            Floor::updateOrCreate(
                ['name' => $floor['name']],
                [
                    'name' => $floor['name'],
                    'number' => $floor['number'],
                    'created_by' => $createdBy,
                ]
            );
        }
    }
}