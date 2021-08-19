<?php


namespace App\Classes;

use App\link;



class SpLinkMenuCard
{
    const DYNAMIC_ADDRESS = 'http://localhost:8080/target/';
    const STATIC_ADDRESS = 'http://localhost/spaces/';

    public $content = array();
    private $titleOut='';
    private $orientOut='';

    function __construct($thisCardId, $orgId, $publishableLayouts, $thisCardContent )
    {
        $thisLink = new Link();
        $cardLinks = array();
        $linksForThisCard = $thisLink->getLinksForCardId($thisCardId);
        foreach($linksForThisCard as $thisCardLink){
            $linkIsPublishable = false;
            foreach($publishableLayouts as $thisPublishableLayout){
                if($thisPublishableLayout->layout_id == $thisCardLink->layout_link_to){
                    $linkIsPublishable = true;
                    break;
                }
            }
            if($linkIsPublishable){
                $newLink = self::STATIC_ADDRESS.$orgId.'/'.$thisCardLink->layout_link_to;

            }else{
                $newLink = self::DYNAMIC_ADDRESS.$orgId.'/'.$thisCardLink->layout_link_to.'.html';
            }
            $fullLink = array($newLink, $thisCardLink->description);
            array_push($cardLinks, $fullLink);


        }
        if(isset($thisCardContent['linkMenuTitle'])){
            $this->titleOut=$thisCardContent['linkMenuTitle'];
        }else{
            $this->titleOut='';
        }
        if(isset($thisCardContent['orient'])){
            $this->orientOut = $thisCardContent['orient'];
        }else{
            $this->orientOut = 'vertical';
        }
        $this->content = array('links'=>$cardLinks, 'title'=>$this->titleOut, 'orient'=>$this->orientOut);


    }
    public function getCardContent(){
        return $this->content;
    }

}
