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
        $str = '<td class="item"> <span
                style="color: rgb(51, 51, 51); font-family: &quot;Amazon Ember&quot;, Arial, sans-serif; font-size: small; background-color: rgb(255, 255, 255);">This
                fashionable and stylish Elastic Knee Compression Sleeve Support with a 4 way stretch capability, offering all
                around superior protection is a must have for runners, weightlifters, or professional athletes.</span><br
                style="box-sizing: border-box; color: rgb(51, 51, 51); font-family: &quot;Amazon Ember&quot;, Arial, sans-serif; font-size: small; background-color: rgb(255, 255, 255);"><span
                style="color: rgb(51, 51, 51); font-family: &quot;Amazon Ember&quot;, Arial, sans-serif; font-size: small; background-color: rgb(255, 255, 255);">Sports
                usage: running, basketball, soccer, football, golf, cycling, tennis, hiking, volleyball, skiing and much
                more</span><br
                style="box-sizing: border-box; color: rgb(51, 51, 51); font-family: &quot;Amazon Ember&quot;, Arial, sans-serif; font-size: small; background-color: rgb(255, 255, 255);"><span
                style="color: rgb(51, 51, 51); font-family: &quot;Amazon Ember&quot;, Arial, sans-serif; font-size: small; background-color: rgb(255, 255, 255);">The
                breathable knitted fabric is strong, can gently close to the skin, regulate humidity.</span>
            <div><span
                    style="color: rgb(51, 51, 51); font-family: &quot;Amazon Ember&quot;, Arial, sans-serif; font-size: small; background-color: rgb(255, 255, 255);"><br></span>
            </div>
            <div>
                <ul class="a-unordered-list a-vertical a-spacing-none"
                    style="box-sizing: border-box; margin: 0px 0px 0px 18px; color: rgb(17, 17, 17); padding: 0px; font-family: &quot;Amazon Ember&quot;, Arial, sans-serif; font-size: 13px; background-color: rgb(255, 255, 255);">
                    <li style="box-sizing: border-box; list-style: disc; overflow-wrap: break-word; margin: 0px;"><span
                            class="a-list-item" style="box-sizing: border-box;">Reduces inflammation/swelling, soreness,
                            stiffness, has heating effect for muscular recovery. Suitable for all sports activities that involve
                            a great amount of stress on the joints like: running, basketball, soccer, football, golf, cycling,
                            tennis, hiking, volleyball, skiing and much more</span></li>
                    <li style="box-sizing: border-box; list-style: disc; overflow-wrap: break-word; margin: 0px;"><span
                            class="a-list-item" style="box-sizing: border-box;">Provide strong absorbent ability, high elastic
                            soft fabric let you wearing comfortable</span></li>
                    <li style="box-sizing: border-box; list-style: disc; overflow-wrap: break-word; margin: 0px;"><span
                            class="a-list-item" style="box-sizing: border-box;">Aid in recovery, runners &amp; jumpers knee,
                            arthritis, tendonitis</span></li>
                    <li style="box-sizing: border-box; list-style: disc; overflow-wrap: break-word; margin: 0px;"><span
                            class="a-list-item" style="box-sizing: border-box;">Machine Washable: The lightweight compression
                            sleeves are machine washable, 100% awesome and easy to care for, One Size fits all, Good for men and
                            women</span></li>
                    <li style="box-sizing: border-box; list-style: disc; overflow-wrap: break-word; margin: 0px;"><span
                            class="a-list-item" style="box-sizing: border-box;">Size: length - 10.6" (27cm), width - 6.7"
                            (17cm)</span></li>
                </ul>
            </div>
        </td>';

        $data = preg_split('/<[^>]*>/', $str);
        $trimmed_array = array_map('trim', $data);
        $tempArr = [];
        for ($i=0; $i < count($trimmed_array); $i++) { 
            if (strpos($trimmed_array[$i],':') !== false) {
                // echo 'yes';
                array_push($tempArr, trim($trimmed_array[$i]));
            }
        }

        return $tempArr;
    }
}
