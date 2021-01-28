<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InstanceParams extends Model
{
    function createInstanceParam($key, $value, $instanceId, $isCss){
//        $newParam = new InstanceParams;
//        $newParam->card_instance_id = $instanceId;
//        $newParam->parameter_key=$key;
//        $newParam->parameter_value=$value;
//        $newParam->isCss=$isCss;
//        $newParam->save();

            $newCardInstanceId =DB::table('instance_params')->insertGetId([
                'card_instance_id'=>$instanceId,
                'parameter_value'=>$value,
                'parameter_key'=>$key,
                'card_instance_id'=>$instanceId,
                'isCss'=>$isCss,
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ]);
    }
    function getCardInstanceParams($CardId){
        if(DB::table('instance_params')->where([
            ['card_instance_id','=',$CardId]
        ])->exists()
        ){
            return  DB::table('instance_params')->where([
                ['card_instance_id','=',$CardId]
            ])->get();
        }else{
            return [];
        }
    }
}
