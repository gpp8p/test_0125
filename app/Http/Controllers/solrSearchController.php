<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

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
        $client = new Client();
        $query = "http://localhost:8983/solr/testCollection2/select?q=".$thisQuery;
        $response = $client->get($query);
        $body = $response->getBody();
        $responseContent = $body->getContents();
        $decodedResponseContent = json_decode($responseContent);

        return "ok";
    }
}
