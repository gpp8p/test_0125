<?php


namespace App\Classes;

use App\Classes\SpRichTextCard;
class SpCard
{
    public $thisCardCss = "";
    public $thisCardProperties = "";
    public $thisCardContent = array();
    public $thisCardCol = 0;
    public $thisCardRow = 0;
    public $thisCardHeight = 0;
    public $thisCardWidth = 0;
    public $thisCardId = 0;
    public $thisCardPosition;
    private $orgId;




    function __construct($thisCardArray, $orgId, $publishableLayouts){
        $thisCardCss = '';
        $thisCardProperties = '';
        $thisCardContent = array();
        foreach ($thisCardArray as $thisCard){
            if ($thisCard[2] == 1) {
                $thisCardCss = $thisCardCss . $thisCard[1];
            } else {
                $thisCardProperties = $thisCardProperties . $thisCard[1];
                $thisCardContent[$thisCard[0]] = $thisCard[1];
            }
            $thisCardIsCss = $thisCard[2];
            $thisCardParameterKey = $thisCard[0];
            $thisCardComponent = $thisCard[3];

            $this->thisCardCol = $thisCard[4];
            $this->thisCardRow = $thisCard[5];
            $this->thisCardHeight = $thisCard[6];
            $this->thisCardWidth = $thisCard[7];
            $this->thisCardId = $thisCard[8];
        }
        $thisCardPosition = array($this->thisCardRow, $this->thisCardCol, $this->thisCardHeight, $this->thisCardWidth);
        $this->orgId = $orgId;
        switch($thisCardComponent){
            case "RichText":{
                $thisSpRichTextCard = new SpRichTextCard($this->thisCardId, $orgId, $publishableLayouts, $thisCardContent );
                $this->thisCardContent = $thisSpRichTextCard->getCardContent();
            }
        }

    }

    
    public function getCssGridParams(){
        return $this->computeGridCss($this->thisCardRow, $this->thisCardCol, $this->thisCardHeight, $this->thisCardWidth) . ";";
    }
    private function getCardParameters(){
        return array(
            'style' => $this->getCssGridParams() . $this->thisCardCss,
            'properties' => $this->thisCardProperties,
            'content' => $this->thisCardContent
        );
    }
    private function getCardPosition(){
        return array($this->thisCardRow, $this->thisCardCol, $this->thisCardHeight, $this->thisCardWidth);
    }
    public function getCardData(){
        return array(
            'id' => $this->thisCardId,
            'card_component' => $this->thisCardComponent,
            'card_parameters' => $this->getCardParameters(),
            'card_position' => $this->getCardPosition()
        );
    }
    private function computeGridCss($row, $col, $height, $width){
        $startRow = $row;
        $startColumn = $col;
        $endRow=0;
        $endCol = 0;

        if($height==1){
            $endRow = $row;
        }else{
            $endRow = $row+$height;
        }
        $endCol=$startColumn+$width;
        $thisCss = "grid-area:".$startRow."/".$startColumn."/".$endRow."/".$endCol;
        return $thisCss;

    }
}
