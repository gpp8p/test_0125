<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use App\Classes\Constants;



class Solr extends Model
{
    public function addFileToCollection($collectionName, $layoutId, $cardId, $fileLocation, $keyWords,$accessType, $documentType ){
        $client = new Client();
        $thisConstants = new Constants;
        $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/update/extract?literal.id=".$layoutId."&literal.cardId=".$cardId;
        if(strlen($keyWords)>0){
            $query = $query."&literal.keywords=".$keyWords;
        }
        if(strlen($accessType)>0){
            $query = $query."&literal.accessType=".$accessType;
        }
        if(strlen($documentType)>0){
            $query = $query."&literal.documentTypeType=".$documentType;
        }
        $t=time();
        $createDate = date("Ymd",$t);
        $query=$query."&literal.create_date=".$createDate;
        $query = $query."&commit=true";
        $filePath = $thisConstants->Options['fileBase'].$fileLocation;
        $thisFile = fopen($filePath, 'r');
        $client = new \GuzzleHttp\Client();


        $request = $client->post( $query, [
            'headers' => [],
            'multipart' => [
                [
                    'name'     => 'myfile',
                    'contents' => $thisFile,
                ]
            ]
        ]);



    }

    public function sendQueryToSolr($thisQuery){

        $thisConstants = new Constants;
        $client = new Client();
        $query = $thisConstants->Options['solrBase'].$thisConstants->Options['collection']."/select?q=".$thisQuery;
        $response = $client->get($query);
        $body = $response->getBody();
        $responseContent = $body->getContents();
        $decodedResponseContent = json_decode($responseContent);
        return $decodedResponseContent;

    }


}
