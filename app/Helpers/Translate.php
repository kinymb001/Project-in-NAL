<?php
use Stichoza\GoogleTranslate\GoogleTranslate;

    function translate($language ,$data){
        $translate = new GoogleTranslate();
        $languages = config('app.language_array');

        if (in_array($language,$languages)){
            return $translate->setSource('en')->setTarget($language)->translate($data);
        }
        return response()->json('languages not support!');
    }
