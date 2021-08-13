<?php


namespace App\Classes;


use App\link;

class SpRichTextCard extends SpCard
{
    function __construct($thisCard, $orgId, $publishableLayouts){
        parent::__construct($thisCard, $orgId);
        $orgDirectory = '/images/'.$orgId;
        $thisLink = new link();
        $cardLinks = $thisLink->getLinksForCardId($thisCard[8]);
        if(isset($thisCardContent['cardText'])){
            $content = $thisCardContent['cardText'];
            foreach($cardLinks as $thisCardLink){
                if($thisCardLink->type=="U"){
                    if(!array_search($thisCardLink->layout_link_to, $publishableLayouts)){
                        $newLink = $this->dynamicAddress.'/'.$thisCardLink->layout_link_to;

                    }else{
                        $newLink = $this->staticAddress.$orgId.'/'.$thisCardLink->layout_link_to.'.html';
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
            $thisCardContent['cardText']= $content;

        }else{
            $thisCardContent['cardText']='';
        }
    }

}
