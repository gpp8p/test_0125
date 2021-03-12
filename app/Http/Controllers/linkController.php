<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class linkController extends Controller
{
    public function getLinksByCardId(Request $request){
        $inData =  $request->all();
        $thisCardId = $inData['cardId'];
        $thisLink = new link();
        return $thisLink->getLinksForCardId($thisCardId);
    }
}
