<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        $web = file_get_contents('https://www.econoco.com/niki-female-pose-3-w-oval-head/');
        $regex = '/"data":\s\[(.*?)]/';
        preg_match( $regex, $web, $matches );
        $striped = stripslashes($matches[0]);
        $replacedD = str_replace(['"data": [',']'], '',$striped);
        
        $regex2 = '/"full":\"(.*?)"/';
        preg_match_all( $regex2, $replacedD, $matches2 );

        $striped2 = [];
        $replacing = ['"full":','\"','"'];
        $replacer = ["","",''];
        for ($i=0; $i < count($matches2[0]); $i++) { 
            $str = stripslashes($matches2[0][$i]);
            $newPhrase = str_replace($replacing, $replacer, $str);
            array_push($striped2, $newPhrase);
        }
        
        return array_unique($striped2);
    }

    public function test2()
    {
        
    }
}
