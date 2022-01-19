<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Solr;

class solrSearchController extends Controller
{
    public function simpleQuery(Request $request){
        if(auth()->user()==null){
            abort(401, 'Unauthorized action.');
        }else{
            $userId = auth()->user()->id;
        }
        $inData =  $request->all();
        $orgId = $inData['orgId'];
        $thisQuery = $inData['query'];
        $thisSolr = new Solr;
        $queryResults = $thisSolr->sendQueryToSolr($thisQuery);
        return "ok";
    }
}
