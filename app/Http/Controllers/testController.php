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
        $web = file_get_contents('https://www.econoco.com/retail-display-mannequins/retail-mannequin-costumers/costumer-with-shoulder-bar-non-adjustable-height/#1492');
        $regex = '/spConfig": ({.*?}),\s/';
        preg_match( $regex, $web, $matches );
        $data = json_decode(mb_convert_encoding($matches[1],'UTF-8','UTF-8'), true);
        $counter = 0;

        return array_key_first($data['attributes']);
        // foreach ($data['attributes'] as $key => $value) {
        //     // echo $data['attributes'][$key]['options'][$counter]['label'];
        //     $arr = $data['attributes'][$key]['options'];
        //     for ($i=0; $i < count($arr); $i++) { 
                
        //     }
        //     $counter++;
        // }
        // return $data['attributes'];
    }

    public function getCateg()
    {
        $web = file_get_contents('https://www.econoco.com/retail-display-mannequins/retail-mannequin-costumers/costumer-with-shoulder-bar-non-adjustable-height/#1492');
    }

    public function test3Mol()
    {
        // $web = file_get_contents('https://www.moriental.com/Black-Lacquered-Solid-Wood-Paperweights_p_1131.html');
        // $regex = '/<li>Dimensions: (.*?)<\/li>/';

        // $web = file_get_contents('https://www.moriental.com/TJ-Global-7-Compartment-Peach-Shape-Traditional-Chinese-Rosewood-Peach-Shape-Wooden-Display-ShelfOrganizer-for-Tea-Pots-Crafts-Figurines-Memorabilia-and-Miniatures--115-x-125_p_3466.html');
        // $regex = '/Approximate Dimensions: (.*")/';

        // $web = file_get_contents('https://www.moriental.com/Unisex-Compression-Thigh-Sleeves-Leggings-Support-Hamstring-Quadriceps-Groin-Pull-and-Strains-Running-Basketball-Tennis-Soccer-Sports-Athletic-Thigh-Support_p_3264.html');
        // $regex = '/Size: (.*")/';

        $web ='https://www.moriental.com/Dull-Polish-Hand-Painted-Feng-Shui-Mini-Maneki-Neko-Lucky-Cat-Blue--Ji-Lucky-_p_3024.html';
        $regex = '/:\s(.*)[<\/>]/';
        
        preg_match( $regex, $web, $matches );

        // return strip_tags($matches[1]);
        return $matches;
    }
}
