<?php

namespace App\Http\Controllers;

use App\Layout;
use Illuminate\Http\Request;
use App\CardInstances;
use App\Group;
use App\Org;
use Illuminate\Support\Facades\DB;

class LayoutController extends Controller
{
    public function createBlankLayout(Request $request){
        $inData =  $request->all();
        $layoutName = $inData['name'];
        $height = $inData['height'];
        $width = $inData['width'];
//        $background = $inData['background'];
        $background = '#DBAA6E';
        $cardParams = [['background-color', $background, true],['color','blue', true]];
        $thisLayout = new Layout;
        $testLayoutDescription = "New Layout for test purposes";
        $newLayoutId = $thisLayout->createBlankLayout($layoutName, $height, $width, $cardParams, $testLayoutDescription);
        $thisCardInstance = new CardInstances;
        $newCardInstances = $thisCardInstance->getLayoutCardInstancesById($newLayoutId);
        return json_encode($newCardInstances);
    }

    public function createNewLayout(Request $request){
        $inData =  $request->all();
        $layoutName = $inData['name'];
        $height = $inData['height'];
        $width = $inData['width'];
        $testLayoutDescription = $inData['description'];
        $background = '#DBAA6E';
        $cardParams = [['background-color', $background, true],['color','blue', true]];
        $thisLayout = new Layout;
        $thisCardInstance = new CardInstances;
        $newLayoutId = $thisLayout->createBlankLayout($layoutName, $height, $width, $cardParams, $testLayoutDescription);
        $newCardInstances = $thisCardInstance->getLayoutCardInstancesById($newLayoutId);
        return json_encode([$newLayoutId]);
    }

    public function createNewLayoutNoBlanks(Request $request){
        $inData =  $request->all();
        $layoutName = $inData['name'];
        $layoutHeight = $inData['height'];
        $layoutWidth = $inData['width'];

        $layoutDescription = $inData['description'];
        $userId = $inData['userId'];
        $orgId = $inData['orgId'];
        $backgroundType = $inData['backgroundType'];
        if($inData['backgroundType']=='I'){
            $backgroundImage = $inData['backgroundImage'];
            $layoutBackgroundColor = '';
        }else{
            $backgroundImage = '';
            $layoutBackgroundColor = $inData['backgroundColor'];
        }
        $layoutInstance = new Layout;
        $newLayoutId = $layoutInstance->createLayoutWithoutBlanks($layoutName, $layoutHeight, $layoutWidth, $layoutDescription, $layoutBackgroundColor, $backgroundImage, $backgroundType);

        $thisGroup = new Group;
        $personalGroupId = $thisGroup->returnPersonalGroupId($userId);
        $newLayoutGroupId = $thisGroup->addNewLayoutGroup($newLayoutId, $layoutName, $layoutDescription);
        $thisGroup->addOrgToGroup($orgId, $newLayoutGroupId);
        $layoutInstance->editPermForGroup($newLayoutGroupId, $newLayoutId, 'view', 1);
        $userPersonalGroupId = $personalGroupId;
        $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'view', 1);
        $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'author', 1);
        $layoutInstance->editPermForGroup($userPersonalGroupId, $newLayoutId, 'admin', 1);

        return json_encode($newLayoutId);

    }

    public function addAccessForGroupToLayout(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['params']['orgId'];
        $groupId = $inData['params']['groupId'];
        $layoutId = $inData['params']['layoutId'];
        $thisGroup = new Group;
        $layoutInstance = new Layout;
        DB::beginTransaction();
        try {
//            $thisGroup->addOrgToGroup($orgId, $groupId);
            $layoutInstance->editPermForGroup($groupId, $layoutId, 'view', 1);
            DB::commit();
        }catch (Throwable $e) {
            DB::rollback();
            abort(500, 'Server error: '.$e->getMessage());
        }
        return "ok";
    }

    public function removePerm(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $groupId = $inData['params']['groupId'];
        $layoutId = $inData['params']['layoutId'];
        $layoutInstance = new Layout;
        try {
            $layoutInstance->removePermForGroup($layoutId, $groupId);
            return "ok";
        }catch (Throwable $e) {
            abort(500, 'Server error: '.$e->getMessage());
        }

    }

    public function test($request){
        $inData =  $request->all();
        return 'ok';
    }

    public function getLayoutList(Request $request){
//        if(auth()->user()==null){
//            abort(401, 'Unauthorized action.');
//        }
        $returnList = array();
        $layoutInstance = new Layout;
        $allLayouts = $layoutInstance->all();
//        foreach($allLayouts as $thisLayout){
//            array_push($returnList, [$thisLayout->id,$thisLayout->menu_label,$thisLayout->description,$thisLayout->height, $thisLayout->width]);
//        }
        return json_encode($allLayouts);
    }
    public function getMySpaces(Request $request){

        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }

        $inData =  $request->all();
        $orgId = $inData['orgId'];
//        $userId = $inData['userId'];
        $thisLayout = new Layout;
        $viewableLayouts = $thisLayout->getViewableLayoutIds($userId, $orgId);
        return json_encode($viewableLayouts);
    }
    public function getViewableLayoutList(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $thisLayout = new Layout;
        $viewableLayouts = $thisLayout->getViewableLayoutIds($userId, $orgId);
        return json_encode($viewableLayouts);
    }

    public function getLayoutPerms(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $layoutId = $inData['layoutId'];
        $thisLayout = new Layout;
        $thisUserPerms = $thisLayout->getUserPermsForLayout($layoutId, $orgId, $userId);
        return $thisUserPerms;
    }
    public function setLayoutPerms(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $layoutId = $inData['params']['layoutId'];
        $groupId = $inData['params']['groupId'];
        $permType = $inData['params']['permType'];
        $permValue = $inData['params']['permValue'];
        $thisLayout = new Layout;
        try {
            $thisLayout->editPermForGroup($groupId, $layoutId, $permType, $permValue);
        } catch (Exception $e) {
            abort(500, 'Server error: '.$e->getMessage());
        }

    }

    public function getOrgLayouts(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $thisLayout = new Layout;
        $thisOrgLayouts = $thisLayout->getOrgLayouts($orgId);
        return json_encode($thisOrgLayouts);
    }

    public function summaryPerms(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $userId = $inData['userId'];
        $orgId = $inData['orgId'];
        $layoutId = $inData['layoutId'];
        $layoutInstance = new Layout;
        $returnPerms = $layoutInstance->summaryPermsForLayout($userId, $orgId, $layoutId);
        return json_encode($returnPerms);
    }

}
