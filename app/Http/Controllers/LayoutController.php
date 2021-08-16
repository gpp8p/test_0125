<?php

namespace App\Http\Controllers;

use App\Layout;
use Illuminate\Http\Request;
use App\CardInstances;
use App\Group;
use App\Org;
use Illuminate\Support\Facades\DB;
use Storage;
use File;
use App\User;
use App\link;
use App\Card;

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
        $userIsAdmin = 1;
        $userNotAdmin = 0;


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
        $newLayoutId = $layoutInstance->createLayoutWithoutBlanks($layoutName, $layoutHeight, $layoutWidth, $layoutDescription, $layoutBackgroundColor, $backgroundImage, $backgroundType, $orgId);

        $thisGroup = new Group;
        try {
            $allUserGroupId = $thisGroup->returnAllUserGroupId();
        } catch (\Exception $e) {
            throw new \Exception('error identifying all user group');
        }
        $personalGroupId = $thisGroup->returnPersonalGroupId($userId);
        $newLayoutGroupId = $thisGroup->addNewLayoutGroup($newLayoutId, $layoutName, $layoutDescription);
        $thisGroup->addOrgToGroup($orgId, $newLayoutGroupId);
        $thisGroup->addUserToGroup($userId, $newLayoutGroupId,$userIsAdmin);
        $layoutInstance->editPermForGroup($allUserGroupId, $newLayoutId, 'view', 1);
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
    public function publishOrg(Request $request){
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $groupInstance = new Group;
        try {
            $allUserGroupId = $groupInstance->allUserId();
        } catch (\Exception $e) {
            abort(500, 'could not find all user group id');
        }
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $userInstance = new User;
        $userFound = $userInstance->findUserByEmail('GuestUser@nomail.com');
        $guestUserId = $userFound[0]->id;


        $thisLayoutInstance = new Layout;
//        $returnedLayouts = $thisLayoutInstance->getPublishableLayoutsForOrg($orgId, $allUserGroupId);
        $returnedLayouts = $thisLayoutInstance->getViewableLayoutIds($userId, $orgId);
//        $viewableLayouts=array();
        if(count($returnedLayouts)==0){
            abort(500, 'no viewable layouts for this org');
        }
        $orgLayouts = '';
        foreach($returnedLayouts as $thisLayout){
//            array_push($viewableLayouts, $thisLayout->layout_id);
            $orgLayouts = $orgLayouts.$thisLayout->id.',';
        }
        $orgLayouts = substr($orgLayouts,0,(strlen($orgLayouts)-1));
        try {
            $viewableLayouts = $thisLayoutInstance->getViewableOrgLayouts($orgLayouts, $allUserGroupId);
        } catch (\Exception $e) {
            abort(500, $e->getMessage()." while getting viewable org layouts");
        }
        $orgDirectory = '/published/'.$orgId;
        if(!Storage::exists($orgDirectory)) {
            Storage::makeDirectory($orgDirectory);
        }else{
            $existingFiles =   Storage::allFiles($orgDirectory);
            Storage::delete($existingFiles);
        }
        $orgImageDirectory = '/published/'.$orgId.'/images';
        if(!Storage::exists($orgImageDirectory)) {
            Storage::makeDirectory($orgImageDirectory);
        }else{
            $existingImageFiles = Storage::allFiles($orgImageDirectory);
            Storage::delete($existingImageFiles);
        }

        foreach($viewableLayouts as $thisViewableLayout){
            if($thisViewableLayout->layout_id==64){
                $a=0;
            }
            if($thisLayoutInstance->isDeleted($thisViewableLayout)) continue;
            try {
                $layoutData = $thisLayoutInstance->publishThisLayout($thisViewableLayout, $orgId, $guestUserId, $orgImageDirectory, $returnedLayouts);
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
            $height = $layoutData['layout']['height'];
            $width = $layoutData['layout']['width'];
            if(isset($layoutData['layout']['backgroundColor'])){
                $backgroundColor = $layoutData['layout']['backgroundColor'];
            }else{
                $backgroundColor = '';
            }
            if(isset($layoutData['layout']['backGroundImageUrl'])){
                $backgroundImageUrl = $layoutData['layout']['backGroundImageUrl'];
            }else{
                $backgroundImageUrl = '';
            }

            $backgroundType = $layoutData['layout']['backgroundType'];
            $thisLayoutCss = $this->layoutCss($height, $width, $backgroundColor, $backgroundImageUrl, $backgroundType, $orgId);
/*
            foreach($layoutData['cards'] as $thisCard){
                $cardType = $thisCard['card_component'];
                switch($cardType){
                    case 'Headline':{
                        break;
                    }
                    case 'RichText':{
                        break;
                    }
                    case 'linkMenu':{
                        break;
                    }
                }
            }
*/
            try {
                $viewHtml = view('layout', ['layoutId' => $thisViewableLayout, 'layoutCss' => $thisLayoutCss, 'cards' => $layoutData['cards']])->render();
            } catch (\Throwable $e) {
                $errorMsg = $e->getMessage();
            }
            $thisOutPutFile = 'published/'.$orgId.'/'.$thisViewableLayout->layout_id.'.html';
            Storage::put($thisOutPutFile, $viewHtml);
        }
        return 'Ok';

    }
    private function layoutCss($height, $width, $backgroundColor, $backgroundImageUrl, $backgroundType, $orgId){
        $urlBase = "http://localhost/spaces/".$orgId;
        $imageBase = $urlBase."/images/";
        $contentBase = $urlBase."/content/";
        $heightSize = number_format((100 / $height) ,2);
        $widthSize = number_format((100 / $width), 2);
        $gridHeightCss = "grid-template-rows: ";
        $gridWidthCss = "grid-template-columns: ";
        $x = 0;
        for($x = 0; $x < $height; $x++) {
            $gridHeightCss = $gridHeightCss.$heightSize."% ";
        }
        for ($y = 0; $y < $width; $y++) {
            $gridWidthCss = $gridWidthCss.$widthSize."% ";
        }
        if($backgroundType=='C'){
            $gridCss =
                "display: grid; grid-gap: 3px; background-color: ".$backgroundColor."; height: 90vh; color: #ffcd90; ".$gridHeightCss.";".$gridWidthCss.";";

        }else{
            $backgroundUrl = $imageBase.$backgroundImageUrl;
            $gridCss = "display: grid; grid-gap: 3px; background-image:".$backgroundUrl."; background-size: cover; background-repeat: no-repeat; background-position: center; height: 90vh; color: #ffcd90; ". $gridHeightCss.";".$gridWidthCss.";";
        }
        return $gridCss;


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

    public function deleteLayout(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $orgId = $inData['orgId'];
        $thisLayout = new Layout;
        $layoutInfo = $thisLayout->getThisLayout($layoutId,$orgId,$userId);
        if(!$layoutInfo['perms']['admin']){
            return 'noAuth';
        }


        $thisLinkInstance = new Link;
        $selectedToLinks = $thisLinkInstance->getLinksToLayout($layoutId);
        $thisCardInstance = new CardInstances;
        foreach($selectedToLinks as $thisSelectedLink){
            $cardType = $thisCardInstance->getCardTypeById($thisSelectedLink->card_instance_id);
            if($cardType[0]->card_component == 'RichText'){
                $orgDirectory = '/spcontent/'.$orgId;
                $contentFileName = '/spcontent/'.$orgId.'/cardText/rtcontent'.$thisSelectedLink->card_instance_id;
                $thisRtContent = Storage::get($contentFileName);
                $textWithLinkRemoved = $this->removeLinkFromRichText($thisRtContent, $thisSelectedLink->link_url);
                Storage::put($contentFileName, $textWithLinkRemoved);
            }
            $thisLinkInstance->deleteLinksToLayout($layoutId);
        }
        $thisLayout->setDelete($layoutId);



    }
    public function removeCardFromLayout(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $orgId = $inData['orgId'];
        $cardId = $inData['cardId'];
        $thisLayout = new Layout;
        $userPerms = $thisLayout->getUserPermsForLayout($layoutId, $orgId, $userId);
        $hasPermission = false;
        foreach($userPerms as $thisPerm){
            if($thisPerm->author>0){
                $hasPermission=true;
            }
            if($thisPerm->admin>0){
                $hasPermission=true;
            }
        }
        if(!$hasPermission){
            return ('not permitted');
        }
        $cardInstance = new CardInstances;
        try {
            $cardInstance->removeCardFromLayout($cardId, $layoutId);
            return 'ok';
        } catch (\Exception $e) {
            return 'error removing card from layout';
        }


    }
    public function deleteCard(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $layoutId = $inData['layoutId'];
        $orgId = $inData['orgId'];
        $cardId = $inData['cardId'];
        $thisLayout = new Layout;
        $userPerms = $thisLayout->getUserPermsForLayout($layoutId, $orgId, $userId);
        $hasPermission = false;
        foreach($userPerms as $thisPerm){
            if($thisPerm->author>0){
                $hasPermission=true;
            }
            if($thisPerm->admin>0){
                $hasPermission=true;
            }
        }
        if(!$hasPermission){
            return ('not permitted');
        }
        $cardInstance = new CardInstances;
        try {
            $cardInstance->deleteCard($cardId);
            return 'ok';
        } catch (\Exception $e) {
            return 'error deleting card';
        }

    }
    public function layoutTest(Request $request){
        $layoutInstance = new Layout;
        $layoutInstance->updateCardInLayout();
        return 'ok';
    }

    private function removeLinkFromRichText($text, $link){
        $linkReferenceLocation = strpos($text, $link);
        $closingTagLocation = strpos($text, '</a>', $linkReferenceLocation);
        $remainingStringCount = strlen($text)-$closingTagLocation;
        $textStartLocation = strpos($text, '>', $linkReferenceLocation);
        $linkTextLength = strlen($text)-$textStartLocation-$remainingStringCount;
        $linkTextContent = substr($text,$textStartLocation+1,$linkTextLength-1 );
        $tagStart = $linkReferenceLocation-9;
        $tagEnd = $closingTagLocation+4;
        $entireTagLength = ($closingTagLocation-$tagStart)+4;
        $entireTag = substr($text, $tagStart,$entireTagLength);
        $replacementText = '['.$linkTextContent.']';
        $newText = substr($text, 0, $tagStart);
        $newText = $newText.'['.$linkTextContent.']';
        $newText = $newText.substr($text,$tagEnd);
        return $newText;
    }

}
