<?php

namespace App;

use App\CardInstances;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;




class Layout extends Model
{
    public function CardInstances()
    {
        return $this->hasMany(CardInstances::class);
    }

    public function createLayoutWithoutBlanks($layoutName, $layoutHeight, $layoutWidth, $layoutDescription, $backgroundColor, $backgroundImage, $backgroundType){
        $newlayoutId =db::table('layouts')->insertgetid([
            'menu_label'=>$layoutName,
            'description'=>$layoutDescription,
            'height'=>$layoutHeight,
            'width'=>$layoutWidth,
            'backgroundColor'=>$backgroundColor,
            'backgroundUrl'=>$backgroundImage,
            'backgroundType'=>$backgroundType,
            'created_at'=>\carbon\carbon::now(),
            'updated_at'=>\carbon\carbon::now()
        ]);
        return $newlayoutId;
    }
//($layoutName, $height, $width, $cardParams, $testLayoutDescription)
    public function createBlankLayout($layoutName, $layoutHeight, $layoutWidth, $cardParams, $layoutDescription)
    {
        $thisNewLayout = new Layout;
        $thisNewLayout->menu_label = $layoutName;
        $thisNewLayout->height = $layoutHeight;
        $thisNewLayout->width = $layoutWidth;
        $thisNewLayout->description = $layoutDescription;

        $newLayoutId =DB::table('layouts')->insertGetId([
            'menu_label'=>$layoutName,
            'description'=>$layoutDescription,
            'height'=>$layoutHeight,
            'width'=>$layoutWidth,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now()
        ]);


//        $thisNewLayout->save();
        $totalNumberOfCells = $layoutHeight * $layoutWidth;
        $row = 1;
        $column = 1;
        for ($x = 0; $x < $totalNumberOfCells; $x++) {
//            $blankLayoutStyle = "grid-area:" . $row . " / " . $column . " / " . $row . " / " . ($column + 1) . ";" . $blankLayoutBackground;
//            $blankLayoutStyle = $blankLayoutBackground;
//            $fontColorCss = "color: blue;";
//            $newParams = [['key'=>'style', 'value'=>$blankLayoutStyle]];
//            $newParams = [['style',$blankLayoutStyle],[]]
            $thisCardInstance = new CardInstances;
            $thisCardInstance->createCardInstance($newLayoutId, $cardParams, $row, $column, 1,1, 'simpleCard');
//           $fontColorCss = "color: blue;";
//            $newParams = [['key'=>'style', 'value'=>$fontColorCss]];
//            $thisCardInstance = new CardInstances;
//            $thisCardInstance->createCardInstance($thisNewLayout->id, $cardParams, $row, $column, 1,1);

            $column++;
            if($column>$layoutWidth){
                $column=1;
                $row++;
            }
        }
        return $newLayoutId;
    }

    public function getLayoutList(){
        return App/Layout::all();
    }

    public function getAllPermsForUser($userId, $layoutId, $orgId){

        $query = "select distinct users.name, groups.group_label, groups.id as group_id, perms.view, perms.admin, perms.author, perms.opt1, perms.opt2, perms.opt3 from layouts, groups, usergroup, users, userorg, org, perms ".
            "where perms.layout_id = layouts.id ".
            "and perms.group_id = groups.id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and layouts.id=? ".
            "and users.id = ? ".
            "and org.id = ? ";

        $retrievedPerms  =  DB::select($query, [$layoutId, $userId, $orgId]);
        return $retrievedPerms;


    }

    public function getPermsSummaryForUser($userId, $layoutId, $orgId){

        $query = "select distinct sum(perms.view) as view, sum(perms.admin) as admin, sum(perms.author) as author, sum(perms.opt1) as opt1, sum(perms.opt2) as opt2, sum(perms.opt3) as opt3 from layouts, groups, usergroup, users, userorg, org, perms ".
            "where perms.layout_id = layouts.id ".
            "and perms.group_id = groups.id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and layouts.id=? ".
            "and users.id = ? ".
            "and org.id = ? ";

        $retrievedPerms  =  DB::select($query, [$layoutId, $userId, $orgId]);
        return $retrievedPerms;
    }

    public function editPermForGroup($groupId, $layoutId, $permType, $permValue){

        try {
            DB::table('perms')
                ->updateOrInsert(
                    ['layout_id' => $layoutId, 'group_id' => $groupId],
                    [$permType => $permValue, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]
                );
            return 'ok';
        } catch (Exception $e) {
            throw $e;
        }

    }

    public function removePermForGroup($layoutId, $groupId){
        $query = "delete from perms where group_id = ? and layout_id=?";
        try {
            $deletedPerms  =  DB::select($query, [$groupId, $layoutId]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getViewableLayoutIds($userId, $orgId){

        $query = "select distinct layouts.description, layouts.id, layouts.menu_label, layouts.height, layouts.width from layouts, perms where layouts.id in ( ".
            "select distinct layouts.id from layouts, groups, usergroup, users, userorg, org, perms ".
            "where perms.layout_id = layouts.id ".
            "and perms.group_id = groups.id ".
            "and groups.id > 1 ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and perms.view=1 ".
            "and users.id = ? ".
            "and org.id = ?) ".
            "and perms.view=1 ";

        $retrievedLayouts  =  DB::select($query, [$userId, $orgId]);
        return $retrievedLayouts;

    }

    public function getLayoutGroups($layoutId, $orgId, $userId){
        $query = "select groups.description, groups.id, perms.view, perms.author, perms.admin, perms.opt1, perms.opt2, perms.opt3 from groups, perms, users, usergroup, userorg, org ".
            "where groups.id = perms.group_id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and org.id = ? ".
            "and users.id=? ".
            "and perms.layout_id = ?";

        $retrievedGroups  =  DB::select($query, [$orgId, $userId, $layoutId]);
        return $retrievedGroups;

    }

    public function getUserPermsForLayout($layoutId, $orgId, $userId){
/*
        $query = "select groups.description, groups.id, perms.view, perms.author, perms.admin, perms.opt1, perms.opt2, perms.opt3 from groups, perms, users, usergroup, userorg, org ".
                "where groups.id = perms.group_id ".
                "and usergroup.group_id = groups.id ".
                "and usergroup.user_id = users.id ".
                "and userorg.user_id = users.id ".
                "and userorg.org_id = org.id ".
                "and org.id = ? ".
                "and users.id=? ".
                "and perms.layout_id = ?";
*/
        $query = "select groups.description, groups.id, perms.view, perms.author, perms.admin, perms.opt1, perms.opt2, perms.opt3 from groups,  perms ".
                "where groups.id = perms.group_id ".
                "and perms.layout_id = ?";


//        $retrievedPerms  =  DB::select($query, [$orgId, $userId, $layoutId]);
        $retrievedPerms  =  DB::select($query, [$layoutId]);
        return $retrievedPerms;
    }

    public function getOrgLayouts($orgId){

        $query = "select distinct layouts.id, layouts.menu_label, layouts.description, layouts.height, layouts.width from layouts, perms, groups, org, grouporg ".
                "where layouts.id = perms.layout_id ".
                "and perms.view=1 ".
                "and perms.group_id = groups.id ".
                "and grouporg.group_id = groups.id ".
                "and grouporg.org_id = ?";

        $retrievedLayouts  =  DB::select($query, [$orgId]);
        return $retrievedLayouts;
    }
    public function getPublishableLayoutsForOrg($orgId, $allUserGroup){
        $query = "select perms.layout_id from perms, grouporg ".
            "where perms.group_id = grouporg.group_id ".
            "and grouporg.org_id = ? ".
            "and perms.view=1 ".
            "and perms.group_id=?";
        $retrievedLayouts  =  DB::select($query, [$orgId, $allUserGroup]);
        return $retrievedLayouts;

    }

    public function summaryPermsForLayout($userId, $orgId, $layoutId){
/*
        $query = "select sum(perms.view) as viewperms, sum(perms.author) as authorperms, sum(perms.admin) as adminperms, ".
            " sum(perms.opt1) as opt1perms, sum(perms.opt2) as opt2perms, sum(perms.opt3) as opt3perms ".
            "from groups, perms, users, usergroup, userorg, org ".
            "where groups.id = perms.group_id ".
            "and usergroup.group_id = groups.id ".
            "and usergroup.user_id = users.id ".
            "and userorg.user_id = users.id ".
            "and userorg.org_id = org.id ".
            "and org.id = ? ".
            "and users.id=? ".
            "and perms.layout_id = ?";
*/
        $query = "select sum(perms.view) as viewperms, sum(perms.author) as authorperms, sum(perms.admin) as adminperms, ".
            "sum(perms.opt1) as opt1perms, sum(perms.opt2) as opt2perms, sum(perms.opt3) as opt3perms ".
            "from perms, groups, usergroup, grouporg ".
            "where groups.id = perms.group_id ".
            "and usergroup.user_id = ? ".
            "and usergroup.group_id = groups.id ".
            "and perms.group_id = groups.id ".
            "and grouporg.group_id = groups.id ".
            "and grouporg.org_id = ? ".
            "and perms.layout_id = ?";

//        $retrievedPerms  =  DB::select($query, [$orgId, $userId, $layoutId]);
        $retrievedPerms  =  DB::select($query, [$userId, $orgId, $layoutId]);
        return $this->booleanPerms($retrievedPerms[0]);
    }


    protected function booleanPerms($perms){
        $returnPerms = array('view'=>false, 'author'=>false, 'admin'=>false, 'opt1'=>false, 'opt2'=>false, 'opt3'=>false);
        if($perms->viewperms>0){
            $returnPerms['view']=true;
        }
        if($perms->authorperms>0){
            $returnPerms['author']=true;
        }
        if($perms->adminperms>0){
            $returnPerms['admin']=true;
        }
        if($perms->opt1perms>0){
            $returnPerms['opt1']=true;
        }
        if($perms->opt2perms>0){
            $returnPerms['opt2']=true;
        }
        if($perms->opt3perms>0){
            $returnPerms['opt3']=true;
        }
        return $returnPerms;
    }

    public function getThisLayout($layoutId, $orgId, $userId)
    {
//    public function getLayoutById(Request $request){
//        $inData =  $request->all();
//        $layoutId = $inData['layoutId'];
//        $orgId = $inData['orgId'];
//        $userId = $inData['userId'];
        $layoutInstance = new Layout;
        $layoutInfo = $layoutInstance->where('id', $layoutId)->get();
        $thisLayoutDescription = $layoutInfo[0]->description;
        $thisLayoutWidth = $layoutInfo[0]->width;
        $thisLayoutHeight = $layoutInfo[0]->height;
        $thisLayoutBackgroundColor = $layoutInfo[0]->backgroundColor;
        $thisLayoutImageUrl = $layoutInfo[0]->backgroundUrl;
        $thisLayoutBackgroundType = $layoutInfo[0]->backgroundType;
        $thisLayoutLabel = $layoutInfo[0]->menu_label;
        $thisCardInstance = new CardInstances;
        $thisLayoutCardInstances = $thisCardInstance->getLayoutCardInstancesById($layoutId, $orgId);
        if ($thisLayoutCardInstances == null) {
            $layoutProperties = array('description' => $thisLayoutDescription, 'menu_label' => $thisLayoutLabel, 'height' => $thisLayoutHeight, 'width' => $thisLayoutHeight, 'backgroundColor' => $thisLayoutBackgroundColor, 'backGroundImageUrl' => $thisLayoutImageUrl, 'backgroundType' => $thisLayoutBackgroundType);
            $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
            $returnData = array('cards' => [], 'layout' => $layoutProperties, 'perms' => $thisLayoutPerms);
            return $returnData;
        }
        $cardsReadIn = array();
        $cardSubElementProperties = array();
        $allCardInstances = array();
        foreach ($thisLayoutCardInstances as $card) {
            $thisId = strval($card->id);
            $thisCardData = array($card->parameter_key, $card->parameter_value, $card->isCss, $card->card_component, $card->col, $card->row, $card->height, $card->width, $card->id);
            if ($card->dom_element == 'main') {
                if (!array_key_exists($thisId, $cardsReadIn)) {
                    $cardsReadIn[$thisId] = [$thisCardData];
                } else {
                    array_push($cardsReadIn[$thisId], $thisCardData);
                }
            } else {
                if (!array_key_exists($thisId, $cardSubElementProperties)) {
                    $cardSubElementProperties[$thisId][$card->dom_element] = array();
                    array_push($cardSubElementProperties[$thisId][$card->dom_element], $thisCardData);
                } else {
                    array_push($cardSubElementProperties[$thisId][$card->dom_element], $thisCardData);
                }
            }
        }
        foreach ($cardsReadIn as $thisCardArray) {
            $thisCardCss = "";
            $thisCardProperties = "";
            $thisCardContent = array();
            foreach ($thisCardArray as $thisCard) {
                if ($thisCard[2] == 1) {
                    $thisCardCss = $thisCardCss . $thisCard[1];
                } else {
                    $thisCardProperties = $thisCardProperties . $thisCard[1];
                    $thisCardContent[$thisCard[0]] = $thisCard[1];
                }
                $thisCardIsCss = $thisCard[2];
                $thisCardParameterKey = $thisCard[0];
                $thisCardComponent = $thisCard[3];
                if ($thisCardComponent == "linkMenu") {
                    $thisLink = new link();
                    $cardLinks = $thisLink->getLinksForCardId($thisCard[8]);
                    $thisCardContent['availableLinks'] = $cardLinks;
                }
                $thisCardCol = $thisCard[4];
                $thisCardRow = $thisCard[5];
                $thisCardHeight = $thisCard[6];
                $thisCardWidth = $thisCard[7];
                $thisCardId = $thisCard[8];
            }
            $cssGridParams = $this->computeGridCss($thisCardRow, $thisCardCol, $thisCardHeight, $thisCardWidth) . ";";
            $thisCardParameters = array(
                'style' => $cssGridParams . $thisCardCss,
                'properties' => $thisCardProperties,
                'content' => $thisCardContent
            );
            $thisCardPosition = array($thisCardRow, $thisCardCol, $thisCardHeight, $thisCardWidth);
            $thisCardData = array(
                'id' => $thisCardId,
                'card_component' => $thisCardComponent,
                'card_parameters' => $thisCardParameters,
                'card_position' => $thisCardPosition
            );
            array_push($allCardInstances, $thisCardData);
        }
        $subElementStyles = array();
        foreach ($cardSubElementProperties as $key => $value) {
            $cardSubElement = $value;
            $cardId = $key;
            $thisSubElementStyle = '';
            foreach ($cardSubElement as $key => $value) {
                foreach ($value as $styleElement) {
                    $thisSubElementStyle = $thisSubElementStyle . $styleElement[1];
                }
                if (!array_key_exists($cardId, $subElementStyles)) {
                    $subElementStyles[$cardId][$key] = array();
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                } else {
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                }
            }
        }
        foreach ($allCardInstances as $key => $value) {
            $thisCardId = $key;
            foreach ($subElementStyles as $key => $value) {
                if ($allCardInstances[$thisCardId]['id'] == $key) {
                    $allCardInstances[$thisCardId]['elementStyles'] = $value;
                }
            }
        }
        $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
        $layoutProperties = array('description' => $thisLayoutDescription, 'menu_label' => $thisLayoutLabel, 'height' => $thisLayoutHeight, 'width' => $thisLayoutHeight, 'backgroundColor' => $thisLayoutBackgroundColor, 'backGroundImageUrl' => $thisLayoutImageUrl, 'backgroundType' => $thisLayoutBackgroundType);
        $returnData = array('cards' => $allCardInstances, 'layout' => $layoutProperties, 'perms' => $thisLayoutPerms);
        return $returnData;
    }


    public function publishThisLayout($layoutId, $orgId, $userId)
    {
//    public function getLayoutById(Request $request){
//        $inData =  $request->all();
//        $layoutId = $inData['layoutId'];
//        $orgId = $inData['orgId'];
//        $userId = $inData['userId'];
        $layoutInstance = new Layout;
        $layoutInfo = $layoutInstance->where('id', $layoutId)->get();
        $thisLayoutDescription = $layoutInfo[0]->description;
        $thisLayoutWidth = $layoutInfo[0]->width;
        $thisLayoutHeight = $layoutInfo[0]->height;
        $thisLayoutBackgroundColor = $layoutInfo[0]->backgroundColor;
        $thisLayoutImageUrl = $layoutInfo[0]->backgroundUrl;
        $thisLayoutBackgroundType = $layoutInfo[0]->backgroundType;
        $thisLayoutLabel = $layoutInfo[0]->menu_label;
        $thisCardInstance = new CardInstances;
        $thisLayoutCardInstances = $thisCardInstance->getLayoutCardInstancesById($layoutId, $orgId);
        if ($thisLayoutCardInstances == null) {
            $layoutProperties = array('description' => $thisLayoutDescription, 'menu_label' => $thisLayoutLabel, 'height' => $thisLayoutHeight, 'width' => $thisLayoutHeight, 'backgroundColor' => $thisLayoutBackgroundColor, 'backGroundImageUrl' => $thisLayoutImageUrl, 'backgroundType' => $thisLayoutBackgroundType);
            $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
            $returnData = array('cards' => [], 'layout' => $layoutProperties, 'perms' => $thisLayoutPerms);
            return $returnData;
        }
        $cardsReadIn = array();
        $cardSubElementProperties = array();
        $allCardInstances = array();
        foreach ($thisLayoutCardInstances as $card) {
            $thisId = strval($card->id);
            $thisCardData = array($card->parameter_key, $card->parameter_value, $card->isCss, $card->card_component, $card->col, $card->row, $card->height, $card->width, $card->id);
            if ($card->dom_element == 'main') {
                if (!array_key_exists($thisId, $cardsReadIn)) {
                    $cardsReadIn[$thisId] = [$thisCardData];
                } else {
                    array_push($cardsReadIn[$thisId], $thisCardData);
                }
            } else {
                if (!array_key_exists($thisId, $cardSubElementProperties)) {
                    $cardSubElementProperties[$thisId][$card->dom_element] = array();
                    array_push($cardSubElementProperties[$thisId][$card->dom_element], $thisCardData);
                } else {
                    array_push($cardSubElementProperties[$thisId][$card->dom_element], $thisCardData);
                }
            }
        }
        foreach ($cardsReadIn as $thisCardArray) {
            $thisCardCss = "";
            $thisCardProperties = "";
            $thisCardContent = array();
            foreach ($thisCardArray as $thisCard) {
                if ($thisCard[2] == 1) {
                    $thisCardCss = $thisCardCss . $thisCard[1];
                } else {
                    $thisCardProperties = $thisCardProperties . $thisCard[1];
                    $thisCardContent[$thisCard[0]] = $thisCard[1];
                }
                $thisCardIsCss = $thisCard[2];
                $thisCardParameterKey = $thisCard[0];
                $thisCardComponent = $thisCard[3];
                if ($thisCardComponent == "linkMenu") {
                    $thisLink = new link();
                    $cardLinks = $thisLink->getLinksForCardId($thisCard[8]);
                    $thisCardContent['availableLinks'] = $cardLinks;
                }
                $thisCardCol = $thisCard[4];
                $thisCardRow = $thisCard[5];
                $thisCardHeight = $thisCard[6];
                $thisCardWidth = $thisCard[7];
                $thisCardId = $thisCard[8];
            }
            if($thisCardComponent=='RichText'){
                $thisLink = new link();
                $cardLinks = $thisLink->getLinksForCardId($thisCard[8]);
                if(isset($thisCardContent['cardText'])){
                    $content = $thisCardContent['cardText'];
                    foreach($cardLinks as $thisCardLink){
                        $newLink = 'http://localhost/spaces/'.$orgId.'/'.$thisCardLink->layout_link_to.'.html';
                        $content = str_replace($thisCardLink->link_url, $newLink, $content);
                    }
                    $thisCardContent['cardText']= $content;

                }else{
                    $thisCardContent['cardText']='';
                }

            }
            $cssGridParams = $this->computeGridCss($thisCardRow, $thisCardCol, $thisCardHeight, $thisCardWidth) . ";";
            $thisCardParameters = array(
                'style' => $cssGridParams . $thisCardCss,
                'properties' => $thisCardProperties,
                'content' => $thisCardContent
            );
            $thisCardPosition = array($thisCardRow, $thisCardCol, $thisCardHeight, $thisCardWidth);
            $thisCardData = array(
                'id' => $thisCardId,
                'card_component' => $thisCardComponent,
                'card_parameters' => $thisCardParameters,
                'card_position' => $thisCardPosition
            );
            array_push($allCardInstances, $thisCardData);
        }
        $subElementStyles = array();
        foreach ($cardSubElementProperties as $key => $value) {
            $cardSubElement = $value;
            $cardId = $key;
            $thisSubElementStyle = '';
            foreach ($cardSubElement as $key => $value) {
                foreach ($value as $styleElement) {
                    $thisSubElementStyle = $thisSubElementStyle . $styleElement[1];
                }
                if (!array_key_exists($cardId, $subElementStyles)) {
                    $subElementStyles[$cardId][$key] = array();
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                } else {
                    array_push($subElementStyles[$cardId][$key], $thisSubElementStyle);
                }
            }
        }
        foreach ($allCardInstances as $key => $value) {
            $thisCardId = $key;
            foreach ($subElementStyles as $key => $value) {
                if ($allCardInstances[$thisCardId]['id'] == $key) {
                    $allCardInstances[$thisCardId]['elementStyles'] = $value;
                }
            }
        }
        $thisLayoutPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
        $layoutProperties = array('description' => $thisLayoutDescription, 'menu_label' => $thisLayoutLabel, 'height' => $thisLayoutHeight, 'width' => $thisLayoutHeight, 'backgroundColor' => $thisLayoutBackgroundColor, 'backGroundImageUrl' => $thisLayoutImageUrl, 'backgroundType' => $thisLayoutBackgroundType);
        $returnData = array('cards' => $allCardInstances, 'layout' => $layoutProperties, 'perms' => $thisLayoutPerms);
        return $returnData;
    }


    private function computeGridCss($row, $col, $height, $width){
        $startRow = $row;
        $startColumn = $col;
        $endRow=0;
        $endCol = 0;

        if($height==1){
            $endRow = $row;
        }else{
            $endRow = $row+$height;
        }
        $endCol=$startColumn+$width;
        $thisCss = "grid-area:".$startRow."/".$startColumn."/".$endRow."/".$endCol;
        return $thisCss;

    }

}
