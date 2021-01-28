<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    function recieveFile(Request $request){
        $inData =  $request->all();
        $path = $request->file('file')->store('file');
        $path = str_replace('file/', '', $path);
        return $path;
    }

    function recieveFileCk(Request $request){
        $inData =  $request->all();
        $path['url'] = 'http://localhost:8000/storage/'.$request->file('upload')->store('file');
        $rval = json_encode($path);
        return $rval;
    }
}
