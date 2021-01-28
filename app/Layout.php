<?php

namespace App;

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




}
