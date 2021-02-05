<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\ViewType;
use Storage;

class CardInstances extends Model
{
    public function viewType()
    {
        return $this->belongsTo('App\ViewType');
    }

    public function params()
    {
        return $this->hasMany(InstanceParams::class);
    }

    public function getLayoutCardInstances($layoutName){
        $query = "select instance.id,parameter_key, parameter_value, card_component from card_instances as instance, instance_params as params, layouts as layouts ".
                "where params.card_instance_id = instance.id ".
                "and instance.layout_id = layouts.id ".
                "and layouts.menu_label = ? ".
                "order by instance.id";

        $retrievedCardInstances  =  DB::select($query, [$layoutName]);
        if(count($retrievedCardInstances)>0) {
            return $retrievedCardInstances;
        }else{
            return null;
        }
    }

    public function getLayoutCardInstancesById($layoutId){
        $query = "select instance.id,parameter_key, parameter_value, card_component, isCss, ".
            "instance.col, instance.row, instance.height, instance.width ".
            "from card_instances as instance, instance_params as params, layouts as layouts ".
            "where params.card_instance_id = instance.id ".
            "and instance.layout_id = layouts.id ".
            "and layouts.id = ? ".
            "order by instance.id";

        $retrievedCardInstances  =  DB::select($query, [$layoutId]);

        foreach( $retrievedCardInstances as $thisCardInstance){
            if($thisCardInstance->parameter_key=='cardText'){
                $contentFileName = '/rtcontent/content'.$thisCardInstance->id;
 //               $rtContents = Storage::get('/rtcontent/content5');
                try {
                    $rtContents = Storage::get($contentFileName);
                } catch (\Exception $e) {
                }
//                $rtContent = Storage::disk('local')->get($contentFileName);
            }
        }
        for($c=0; $c<count($retrievedCardInstances); $c++){
            $thisCardInstance = $retrievedCardInstances[$c];
            if($thisCardInstance->parameter_key=='cardText'){
                try {
                    $rtContents = Storage::get($contentFileName);
                    $retrievedCardInstances[$c]->parameter_value=$rtContents;
                } catch (\Exception $e) {
                }
            }
        }


        if(count($retrievedCardInstances)>0) {
            return $retrievedCardInstances;
        }else{
            return null;
        }
    }


    public function createCardInstance($layoutId, $cardParams, $row, $column, $height, $width, $cardType){


        $viewType = ViewType::where('view_type_label', 'Web Browser')->first()->id;
        $newCardInstanceId =DB::table('card_instances')->insertGetId([
            'col'=>$column,
            'row'=>$row,
            'height'=>$height,
            'width'=>$width,
            'layout_id'=>$layoutId,
            'card_component'=>$cardType,
            'view_type_id'=>$viewType,
            'card_component'=>$cardType,

            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);



//        $thisCardInstance->save();
//        $newCardInstanceId = $thisCardInstance->id;
        foreach($cardParams as $thisParam){
            $thisInstanceParams = new InstanceParams;
            $thisInstanceParams->createInstanceParam($thisParam[0], $thisParam[1],$newCardInstanceId, $thisParam[2]);
        }
        return $newCardInstanceId;

    }




}
