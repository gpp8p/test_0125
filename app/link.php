<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class link extends Model
{
    public function getLinksForCardId($cardId){
        $query = "select isExternal, link_url, layout_link_to, description from links where card_instance_id = ?";
        try {
            $linkInfo = DB::select($query, [$cardId]);
            return $linkInfo;
        } catch (Exception $e) {
            throw new Exception('error ".$e.getMessage()."loading links for'.$cardId);
        }
    }
}
