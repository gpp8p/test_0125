<?php

namespace App;

use Facade\Ignition\Support\Packagist\Package;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InstanceParams extends Model
{
    function createInstanceParam($key, $value, $instanceId, $isCss, $domElement){
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
                'dom_element'=>$domElement,
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
    function hasInstanceParam($cardId, $paramKey){
        if(DB::table('instance_params')->where([
            ['card_instance_id','=',$cardId]
        ])->exists()
        ){
            $params =   DB::table('instance_params')->where([
                ['card_instance_id','=',$cardId],
                ['parameter_key', '=', $paramKey]
            ])->get();
            if(count($params)>0){
                return $params[0]->id;
            }else{
                return -1;
            }
        }else{
            return -1;
        }
    }
    function updateInstanceParam($paramId,$key, $value, $instanceId, $isCss, $domElement){
        $query = 'update instanceParams set parameter_key = ?, parameter_value = ?, card_instance_id = ?, isCss=?, $domElement = ? where id = ?';

        try {
            DB::select($query, [$key, $value, $instanceId, $isCss, $domElement, $paramId]);
        } catch (Exception $e) {
            throw new Exception('error - could not update an instance param');
        }
    }
}
