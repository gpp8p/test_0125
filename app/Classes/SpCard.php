<?php


namespace App\Classes;


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
    private $orgId;




    function __construct($thisCard, $orgId){
        $thisCardCss = '';
        $thisCardProperties = '';
        if ($thisCard[2] == 1) {
            $thisCardCss = $thisCardCss . $thisCard[1];
        } else {
            $thisCardProperties = $thisCardProperties . $thisCard[1];
            $thisCardContent[$thisCard[0]] = $thisCard[1];
        }
        $thisCardIsCss = $thisCard[2];
        $thisCardParameterKey = $thisCard[0];
        $thisCardComponent = $thisCard[3];

        $thisCardCol = $thisCard[4];
        $thisCardRow = $thisCard[5];
        $thisCardHeight = $thisCard[6];
        $thisCardWidth = $thisCard[7];
        $thisCardId = $thisCard[8];
        $this->orgId = $orgId;

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
