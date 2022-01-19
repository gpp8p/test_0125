<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use App\Classes\Constants;

class Solr extends Model
{
    public function addFileToCollection($collectionName, $layoutId, $cardId, $fileLocation, $keywords  ){
        $client = new Client();

    }

    public function sendQueryToSolr($thisQuery){

        $thisContants = new Constants;
        $client = new Client();
        $query = $thisContants->Options['solrBase'].$thisContants->Options['collection']."/select?q=".$thisQuery;
        $response = $client->get($query);
        $body = $response->getBody();
        $responseContent = $body->getContents();
        $decodedResponseContent = json_decode($responseContent);
        return $decodedResponseContent;

    }
}
