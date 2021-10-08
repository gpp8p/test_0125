<?php

use Illuminate\Database\Seeder;

class document_status extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document_status')->insert([
            'document_status'=>'Draft'
        ]);
        DB::table('document_status')->insert([
            'document_status'=>'Approved'
        ]);
        DB::table('document_status')->insert([
            'document_status'=>'Rejected'
        ]);
    }
}
