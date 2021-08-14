<?php


namespace App\Classes;


use App\link;

class SpRichTextCard
{
    const DYNAMIC_ADDRESS = 'http://localhost:8080/target/';
    const STATIC_ADDRESS = 'http://localhost/spaces/';

    public $content;
    function __construct($thisCardId, $orgId, $publishableLayouts, $thisCardContent ){
        $orgDirectory = '/images/'.$orgId;
        $thisLink = new link();
        $cardLinks = $thisLink->getLinksForCardId($thisCardId);
        if(isset($thisCardContent['cardText'])){
            $content = $thisCardContent['cardText'];
            foreach($cardLinks as $thisCardLink){
                if($thisCardLink->type=="U"){
                    $linkIsPublishable = false;
                    foreach($publishableLayouts as $thisPublishableLayout){
                        if($thisPublishableLayout->id == $thisCardLink->layout_link_to){
                            $linkIsPublishable = true;
                            break;
                        }
                    }
                    if($linkIsPublishable){
                        $newLink = self::DYNAMIC_ADDRESS.'/'.$thisCardLink->layout_link_to;

                    }else{
                        $newLink = self::STATIC_ADDRESS.$orgId.'/'.$thisCardLink->layout_link_to.'.html';
                    }
                    $content = str_replace($thisCardLink->link_url, $newLink, $content);
                }else if($thisCardLink->type=="I"){
                    $imageLink = $thisCardLink->link_url;
                    $imageFileNameAt = strpos($imageLink, 'images/'.$orgId.'/');
                    if($imageFileNameAt!=false){
                        $imageFileNameAt = strlen('http://localhost:8000/images/'.$orgId.'/');
                        $imageFileName = substr($imageLink, $imageFileNameAt);
                        $imageSource = $orgDirectory.'/'.$imageFileName;
                        $copyToLocation = '/published/'.$orgId.'/images'.'/'.$imageFileName;
                        Storage::copy($imageSource, $copyToLocation);

                    }

                }
            }

        }else{
            $content='';
        }
    }
    public function getCardContent(){
        return $this->thisCardContent['cardText'];
    }

}
