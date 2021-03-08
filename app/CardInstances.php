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

    public function getLayoutCardInstancesById($layoutId, $orgId){
        $query = "select instance.id,parameter_key, parameter_value, card_component, isCss, dom_element, ".
            "instance.col, instance.row, instance.height, instance.width ".
            "from card_instances as instance, instance_params as params, layouts as layouts ".
            "where params.card_instance_id = instance.id ".
            "and instance.layout_id = layouts.id ".
            "and layouts.id = ? ".
            "order by instance.id, dom_element";

        $retrievedCardInstances  =  DB::select($query, [$layoutId]);

        for($c=0;$c<count($retrievedCardInstances);$c++){
            $thisCardInstance = $retrievedCardInstances[$c];
            if($thisCardInstance->parameter_key=='cardText'){


                $orgDirectory = '/spcontent/'.$orgId;
                $contentFileName = $thisCardInstance->parameter_value;
                try {
                    $retrievedCardInstances[$c]->parameter_value = Storage::get($contentFileName);
                } catch (\Exception $e) {
                    $retrievedCardInstances[$c]->parameter_value = 'Error fetching content file:'.$contentFileName;
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
