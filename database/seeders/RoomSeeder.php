<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerTwo = User::where('email', 'manager2@manager.com')->first();

        $primaryRooms = [
            ['number' => '1101', 'capacity' => 2, 'price' => 12900, 'floor_number' => '1000'],
            ['number' => '1102', 'capacity' => 3, 'price' => 15900, 'floor_number' => '1000'],
            ['number' => '1201', 'capacity' => 2, 'price' => 14900, 'floor_number' => '1001'],
            ['number' => '1202', 'capacity' => 4, 'price' => 19900, 'floor_number' => '1001'],
            ['number' => '1301', 'capacity' => 1, 'price' => 10900, 'floor_number' => '1002'],
            ['number' => '1401', 'capacity' => 2, 'price' => 16900, 'floor_number' => '1003'],
        ];

        foreach ($primaryRooms as $room) {
            $floor = Floor::query()->where('number', $room['floor_number'])->first();

            if (! $floor) {
                continue;
            }

            Room::updateOrCreate(
                ['number' => $room['number']],
                [
                    'number' => $room['number'],
                    'capacity' => $room['capacity'],
                    'price' => $room['price'],
                    'floor_id' => $floor->id,
                    'created_by' => $floor->created_by,
                ]
            );
        }

        if (! $managerTwo) {
            return;
        }

        $managerTwoRooms = [
            ['number' => '2101', 'capacity' => 2, 'price' => 18900, 'floor_number' => '2000'],
            ['number' => '2102', 'capacity' => 3, 'price' => 21900, 'floor_number' => '2000'],
            ['number' => '2201', 'capacity' => 4, 'price' => 24900, 'floor_number' => '2001'],
        ];

        foreach ($managerTwoRooms as $room) {
            $floor = Floor::query()
                ->where('number', $room['floor_number'])
                ->where('created_by', $managerTwo->id)
                ->first();

            if (! $floor) {
                continue;
            }

            Room::updateOrCreate(
                ['number' => $room['number']],
                [
                    'number' => $room['number'],
                    'capacity' => $room['capacity'],
                    'price' => $room['price'],
                    'floor_id' => $floor->id,
                    'created_by' => $managerTwo->id,
                ]
            );
        }
    }
}
