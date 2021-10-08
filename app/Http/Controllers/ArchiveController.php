<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Archive;

class ArchiveController extends Controller
{

    public function getDocumentDefaults(){
        $thisArchive = new Archive;
        try {
            $currentDocumentTypes = $thisArchive->getDocumentTypes();
            $currentFileTypes = $thisArchive->getFileTypes();
            $allDocumentDefaults = array("documentTypes"=>$currentDocumentTypes, "fileTypes"=>$currentFileTypes);
            return json_encode($allDocumentDefaults);
        } catch (\Exception $e) {
            abort(500, $e);
        }
    }

}
