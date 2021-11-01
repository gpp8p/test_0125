<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \File;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{


    function recieveFile(Request $request){
        $inData =  $request->all();
        $org = $inData['org'];
        switch($inData['fileRole']){
            case 'backgroundImage':{
                $path = $request->file('file')->store('file');
                $path = str_replace('file/', '', $path);
                $orgDirectory = '/images/' . $org;
                if (!Storage::exists($orgDirectory)) {
                    Storage::makeDirectory($orgDirectory);
                }
                $copyToLocation = $orgDirectory . '/' . $path;
                Storage::copy('file/' . $path, $copyToLocation);
                $accessLocation = "http://localhost:8000/images/" . $org . "/" . $path;
                break;
            }
            case 'PDF':{
                $path = $request->file('file')->store('file');
                $path = str_replace('file/', '', $path);
                $orgDirectory = '/spcontent/' . $org.'/cardText';
                if (!Storage::exists($orgDirectory)) {
                    Storage::makeDirectory($orgDirectory);
                }
                $copyToLocation = $orgDirectory . '/' . $path;
                Storage::copy('file/' . $path, $copyToLocation);
                $accessLocation = "/spcontent/" . $org . "/cardText/" . $path;


                break;
            }
        }
        return $accessLocation;
    }

    function recieveFileCk(Request $request){
$inData =  $request->all();
        $urlBase = 'http://localhost:8000/';
        $pth = $urlBase.'storage/'.$request->file('upload')->store('file');
        $pth = str_replace('/file', '', $pth);
        $path['url'] = $pth;
        $uploadedFileName = str_replace($urlBase.'storage/','', $pth);
//        $thisFile = new File;
        $publicDirectoryLocation = public_path();
        $storageDirectoryLocation = storage_path();
        $storageLocation = $storageDirectoryLocation.'/app/file/'.$uploadedFileName;
        $publicFileName = $publicDirectoryLocation.'/storage/'.$uploadedFileName;
        File::copy($storageLocation, $publicFileName);
        $rval = json_encode($path);
        return $rval;
    }
}
