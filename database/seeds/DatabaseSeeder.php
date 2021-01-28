<?php

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        $this->call(view_types::class);
        $this->call(User::class);
        $this->call(Org::class);
        $this->call(Groups::class);
        $this->call(Layouts::class);
    }
}
