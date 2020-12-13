<?php

namespace Database\Seeders;

use App\Models\Share_status;
use Illuminate\Database\Seeder;

class ShareStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Share_status::factory()
            ->times(1)
            ->createOne([
                'value' => 'Public'
            ]);
        Share_status::factory()
            ->times(1)
            ->createOne([
                'value' => 'Friends'
            ]);
        Share_status::factory()
            ->times(1)
            ->createOne([
                'value' => 'Private'
            ]);
    }
}
