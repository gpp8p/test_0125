<?php

use Illuminate\Database\Seeder;

class comment_type extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('comment_type')->insert([
            'document_status'=>'Proposed Revision'
        ]);
        DB::table('comment_type')->insert([
            'document_status'=>'Comment'
        ]);
        DB::table('comment_type')->insert([
            'document_status'=>'Proposal'
        ]);
    }
}
