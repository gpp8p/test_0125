<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class link extends Model
{
    public function getLinksForCardId($cardId){
        $query = "select isExternal, link_url, layout_link_to, description, type from links where card_instance_id = ?";
        try {
            $linkInfo = DB::select($query, [$cardId]);
            return $linkInfo;
        } catch (Exception $e) {
            throw new Exception('error ".$e.getMessage()."loading links for'.$cardId);
        }
    }
    public function removeLinksForCardId($cardId, $linkType){
        $query = 'delete from links where card_instance_id = ? and type = ?';
        try {
            DB::select($query, [$cardId, $linkType]);
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' removing old links from '. $cardId);
        }
    }

    public function saveLink($orgId, $layoutId, $cardInstanceId, $description, $linkUrl, $isExternal, $layoutLinkTo, $linkType){
        try {
            $thisOrgId = DB::table('links')->insertGetId([
                'org_id' => 1,
                'layout_id' => $layoutId,
                'card_instance_id' => $cardInstanceId,
                'description' => $description,
                'isExternal' => $isExternal,
                'link_url' => $linkUrl,
                'layout_link_to' => $layoutLinkTo,
                'type'=>$linkType,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getLinksToLayout($toLayoutId){
        $query = "select org_id, layout_id, card_instance_id, link_url from links where layout_link_to = ?";
        try {
            $selectedToLinks = DB::select($query, [$toLayoutId]);
        }catch (Exception $e){
            throw new Exception('error '.$e.getMessage().' removing old links from '. $cardId);
        }
        return $selectedToLinks;
    }
}
