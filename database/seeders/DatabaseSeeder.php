<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    const SEEDER_TO_CALL = [
            UserSeeder::class,
            ShareStatusesSeeder::class,
            RecipeSeeder::class
    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(self::SEEDER_TO_CALL);
    }
}
