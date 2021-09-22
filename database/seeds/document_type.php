<?php

use Illuminate\Database\Seeder;

class document_type extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document_type')->insert([
            'document_type'=>'Agenda'
        ]);
        DB::table('document_type')->insert([
            'document_type'=>'Minutes'
        ]);
        DB::table('document_type')->insert([
            'document_type'=>'Letter'
        ]);
        DB::table('document_type')->insert([
            'document_type'=>'Project Proposal'
        ]);

    }
}
