<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        $web = file_get_contents('https://www.econoco.com/niki-female-pose-3-w-oval-head/');
        $regex = '/"data":\s\[(.*?)]/';
        preg_match($regex, $web, $matches);
        $striped = stripslashes($matches[0]);
        $replacedD = str_replace(['"data": [', ']'], '', $striped);

        $regex2 = '/"full":\"(.*?)"/';
        preg_match_all($regex2, $replacedD, $matches2);

        $striped2 = [];
        $replacing = ['"full":', '\"', '"'];
        $replacer = ["", "", ''];
        for ($i = 0; $i < count($matches2[0]); $i++) {
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
        preg_match($regex, $web, $matches);
        $data = json_decode(mb_convert_encoding($matches[1], 'UTF-8', 'UTF-8'), true);
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

        $web = 'https://www.moriental.com/Dull-Polish-Hand-Painted-Feng-Shui-Mini-Maneki-Neko-Lucky-Cat-Blue--Ji-Lucky-_p_3024.html';
        $regex = '/:\s(.*)[<\/>]/';

        preg_match($regex, $web, $matches);

        // return strip_tags($matches[1]);
        return $matches;
    }

    public function test4Mol()
    {
        $str = '<tr>
        <td class="item" colspan="2" align="center"><table width="100%" cellpadding="5" cellspacing="0" class="alternative">
        <tbody><tr>
        <td class="item"> Chinese Healthy Balls stimulate and exercise your hands muscles by moving both of them in one hand. The balls are metal, hollow and have chiming sounding plates inside. They make crisp and rhythmical sounds when moving.Play them to relax and reduce stress. Each ball is about 2" in diameter. </td>
        </tr>
        </tbody></table></td>
        </tr><tr>
        <td align="center" colspan="2"><table width="100%" cellpadding="0" cellspacing="0">
        </table></td>
        </tr><tr>
        <td class="item" colspan="2">
        <li>SIZE: #2Diameter: 2"</li>
        </td>
        </tr>';

        $data = preg_split('/<[^>]*>/', $str);
        $trimmed_array = array_map('trim', $data);
        $tempArr = [];
        for ($i = 0; $i < count($trimmed_array); $i++) {
            if (strpos($trimmed_array[$i], ':') !== false) {
                // echo 'yes';
                // array_push($tempArr, trim($trimmed_array[$i]));
                if (strpos($trimmed_array[$i], "SIZE: #2Diameter: 2\"") !== false) {
                    $res = 1;
                    break;
                } else {
                    $res = 0;
                }
            }
        }
        echo $res;
        // return $tempArr;


    }

    public function test5Mol(Request $r)
    {
        $web = file_get_contents('https://www.moriental.com/10-Long-Pear-Wood-Churchwarden-Tobacco-Pipe-with-Gift-Box-TP8021-2_p_2756.html');
        $regex = '/Size Approx: (.*")/';
        preg_match($regex, $web, $matches);
        $raw_dims = explode('X', $matches[0]);

        $dims['x'] = isset($raw_dims[0]) ? self::getFloat($raw_dims[0]) : null;
        $dims['y'] = isset($raw_dims[1]) ? self::getFloat($raw_dims[1]) : null;
        $dims['z'] = isset($raw_dims[2]) ? self::getFloat($raw_dims[2]) : null;
        return $dims;
    }

    public static function getFloat(string $string, ?float $default = null): ?float
    {
        if (preg_match('/\d+\.\d+|\.\d+|\d+/', str_replace(',', '', $string), $match_float)) {
            return self::normalizeFloat((float)$match_float[0], $default);
        }
        return null;
    }

    public static function normalizeFloat(?float $float, ?float $default = null): ?float
    {
        $float = round($float, 2);
        return $float > 0.01 ? $float : $default;
    }
}
