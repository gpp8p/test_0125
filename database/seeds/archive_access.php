<?php

use Illuminate\Database\Seeder;

class archive_access extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('archive_access')->insert([
        'access_type'=>'Private'
        ]);

        DB::table('archive_access')->insert([
            'access_type'=>'Layout Only'
        ]);

        DB::table('archive_access')->insert([
            'access_type'=>'Organization'
        ]);

        DB::table('archive_access')->insert([
            'access_type'=>'Public'
        ]);
    }
}
