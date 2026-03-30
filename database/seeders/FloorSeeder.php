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
        $managerTwo = User::where('email', 'manager2@manager.com')->first();

        $managerIds = User::role('manager')
            ->when($managerTwo, fn ($query) => $query->where('id', '!=', $managerTwo->id))
            ->pluck('id')
            ->values();

        $floors = [
            ['name' => 'Ground Floor', 'number' => '1000'],
            ['name' => 'First Floor', 'number' => '1001'],
            ['name' => 'Second Floor', 'number' => '1002'],
            ['name' => 'Third Floor', 'number' => '1003'],
        ];

        if ($managerIds->isNotEmpty()) {
            foreach ($floors as $index => $floor) {
                $createdBy = $managerIds[$index % $managerIds->count()];

                Floor::updateOrCreate(
                    ['number' => $floor['number']],
                    [
                        'name' => $floor['name'],
                        'number' => $floor['number'],
                        'created_by' => $createdBy,
                    ]
                );
            }
        }

        if (! $managerTwo) {
            return;
        }

        $managerTwoFloors = [
            ['name' => 'Manager Two Floor 1', 'number' => '2000'],
            ['name' => 'Manager Two Floor 2', 'number' => '2001'],
        ];

        foreach ($managerTwoFloors as $floor) {
            Floor::updateOrCreate(
                ['number' => $floor['number']],
                [
                    'name' => $floor['name'],
                    'number' => $floor['number'],
                    'created_by' => $managerTwo->id,
                ]
            );
        }
    }
}