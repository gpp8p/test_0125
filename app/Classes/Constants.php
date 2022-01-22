<?php

namespace App\Classes;

class Constants{
    function __construct(){
        $this->Options  = [
                'solrBase'=>'http://localhost:8983/solr/',
                'collection'=>'spaces_test5',
                'fileBase'=>'/Users/georgepipkin/Sites/test_1006/storage/app/'
        ];
    }
}

