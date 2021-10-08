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
            'comment_type'=>'Proposed Revision'
        ]);
        DB::table('comment_type')->insert([
            'comment_type'=>'Comment'
        ]);
        DB::table('comment_type')->insert([
            'comment_type'=>'Proposal'
        ]);
    }
}
