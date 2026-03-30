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
        $fallbackCreatorId = User::role('manager')->value('id')
            ?? User::role('admin')->value('id');

        if (! $fallbackCreatorId) {
            return;
        }

        $roomsByFloor = [
            '1000' => [
                ['number' => '101', 'capacity' => 2, 'price' => 120],
                ['number' => '102', 'capacity' => 3, 'price' => 150],
                ['number' => '103', 'capacity' => 4, 'price' => 180],
            ],
            '1001' => [
                ['number' => '201', 'capacity' => 2, 'price' => 130],
                ['number' => '202', 'capacity' => 3, 'price' => 160],
                ['number' => '203', 'capacity' => 4, 'price' => 190],
            ],
            '1002' => [
                ['number' => '301', 'capacity' => 2, 'price' => 140],
                ['number' => '302', 'capacity' => 3, 'price' => 170],
                ['number' => '303', 'capacity' => 4, 'price' => 200],
            ],
            '1003' => [
                ['number' => '401', 'capacity' => 2, 'price' => 150],
                ['number' => '402', 'capacity' => 3, 'price' => 180],
                ['number' => '403', 'capacity' => 4, 'price' => 210],
            ],
            '2000' => [
                ['number' => '501', 'capacity' => 2, 'price' => 160],
                ['number' => '502', 'capacity' => 3, 'price' => 190],
            ],
            '2001' => [
                ['number' => '601', 'capacity' => 2, 'price' => 170],
                ['number' => '602', 'capacity' => 3, 'price' => 200],
            ],
        ];

        foreach ($roomsByFloor as $floorNumber => $rooms) {
            $floor = Floor::query()->where('number', $floorNumber)->first();

            if (! $floor) {
                continue;
            }

            $createdBy = $floor->created_by ?: $fallbackCreatorId;

            foreach ($rooms as $room) {
                Room::updateOrCreate(
                    ['number' => $room['number']],
                    [
                        'capacity' => $room['capacity'],
                        'price' => $room['price'],
                        'floor_id' => $floor->id,
                        'created_by' => $createdBy,
                    ]
                );
            }
        }
    }
}
