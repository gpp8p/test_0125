<?php

namespace App\Http\Controllers;

use App\Layout;
use Illuminate\Http\Request;
use App\link;
use App\InstanceParams;
use Illuminate\Support\Facades\DB;

class linkController extends Controller
{
    public function getLinksByCardId(Request $request){
        $inData =  $request->all();
        $thisCardId = $inData['cardId'];
        $thisLink = new link();
        return $thisLink->getLinksForCardId($thisCardId);
    }

    
    public function createNewLink(Request $request){
        $inData =  $request->all();
        $thisCardId = $inData['card_instance_id'];
        $thisOrgId = $inData['org_id'];
        $thisLayoutId = $inData['layout_id'];
        $thisDescription = $inData['description'];
        $thisIsExternal = $inData['is_external'];
        $thisLinkUrl = $inData['linkUrl'];
        $thisLayoutLinkTo = $inData['layout_link_to'];
        $linkType = $inData['type'];
        $thisLayout = new Layout;
        $thisLinkInstance = new link;
        try {
            $thisLinkInstance->saveLink($thisOrgId, $thisLayoutId, $thisCardId, $thisDescription, $thisLinkUrl, $thisIsExternal, $thisLayoutLinkTo, $linkType);
            return "ok";
        } catch (\Exception $e) {
            return "Error ".$e;
        }
    }

    public function deleteLink(Request $request){
        $inData =  $request->all();
        $linkIdToDelete = $inData['linkId'];
        $thisLinkInstance = new link;
        $thisLinkInstance->deleteLink($linkIdToDelete);
        return "ok";

    }
    public function updateCardLinks(Request $request){
        $inData =  $request->all();
        $allLinksJson = $inData['allLinks'];
        $allLinks = json_decode($allLinksJson);
        $thisCardId = $inData['card_instance_id'];
        $thisOrgId = $inData['org_id'];
        $thisLayoutId = $inData['layout_id'];
        $thisOrient = $inData['orient'];
        $thisCardTitle = $inData['cardTitle'];
        $thisLinkInstance = new link;
        $thisInstanceParams = new InstanceParams;
        $orientId = $thisInstanceParams->hasInstanceParam($thisCardId, 'orient');
        $cardTitleId = $thisInstanceParams->hasInstanceParam($thisCardId, 'linkMenuTitle');
        DB::beginTransaction();
        if($orientId>0){
            try {
                $thisInstanceParams->updateInstanceParam($orientId, 'orient', $thisOrient, $thisCardId, 0, 'main');
            } catch (\Exception $e) {
                abort(500, 'Server error updating instance_param: '.$e->getMessage());
            }
        }else{
            try {
                $thisInstanceParams->createInstanceParam('orient', $thisOrient, $thisCardId, 0, 'main');
            } catch (\Exception $e) {
                abort(500, 'Server error creating instance_param: '.$e->getMessage());
            }
        }
        if($cardTitleId>0){
            try {
                $thisInstanceParams->updateInstanceParam($orientId, 'linkMenuTitle', $thisCardTitle, $thisCardId, 0, 'main');
            } catch (\Exception $e) {
                abort(500, 'Server error updating instance_param: '.$e->getMessage());
            }
        }else{
            try {
                $thisInstanceParams->createInstanceParam('linkMenuTitle', $thisCardTitle, $thisCardId, 0, 'main');
            } catch (\Exception $e) {
                abort(500, 'Server error creating instance_param: '.$e->getMessage());
            }
        }


        try {
            $thisLinkInstance->removeLinksForCardId($thisCardId, 'U');
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, 'Server error: '.$e->getMessage());
        }
        try {
            foreach ($allLinks as $thisLink) {
                $thisLinkInstance->saveLink(
                    $thisOrgId,
                    $thisLayoutId,
                    $thisCardId,
                    $thisLink->description,
                    $thisLink->link_url,
                    $thisLink->isExternal,
                    $thisLink->layout_link_to,
                    'U');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, 'Server error: '.$e->getMessage());
        }
        DB::commit();
        return 'ok';


    }
}
