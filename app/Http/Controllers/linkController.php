<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\link;

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
        $thisLinkInstance = new link;
        try {
            $thisLinkInstance->saveLink($thisOrgId, $thisLayoutId, $thisCardId, $thisDescription, $thisLinkUrl, $thisIsExternal, $thisLayoutLinkTo, $linkType);
            return "ok";
        } catch (\Exception $e) {
            return "Error ".$e;
        }
    }
}
