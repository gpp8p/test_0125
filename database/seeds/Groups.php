<?php

use Illuminate\Database\Seeder;

class Groups extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $thisGroupId = DB::table('groups')->insertGetId([
            'group_label'=>'AllUsers',
            'description'=>'All Users',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
        $thisUserId = DB::table('users')->where('name', 'GuestUser')->first()->id;
        DB::table('usergroup')->insert([
            'user_id'=>$thisUserId,
            'group_id'=>$thisGroupId,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now(),
            'is_admin'=>false
        ]);
        $thisGroupId = DB::table('groups')->insertGetId([
            'group_label'=>'gpp8pvirginia@gmail.com',
            'description'=>'spaces_admin personal group',
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);
        $thisUserId = DB::table('users')->where('name', 'spaces_admin')->first()->id;
        DB::table('usergroup')->insert([
            'user_id'=>$thisUserId,
            'group_id'=>$thisGroupId,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now(),
            'is_admin'=>true
        ]);


    }
}
