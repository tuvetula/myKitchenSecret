<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create my profil admin
        user::factory()
            ->createOne([
                'email' => 'romain.marcant@gmail.com',
                'name'=> 'marcant',
                'first_name' => 'romain',
                'is_admin' => 1
            ]);

        //create 49 another random profil
        User::factory()
            ->times(49)
            ->create();
    }
}
