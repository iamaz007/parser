<?php
    // $decodeImage = [];
    $web = file_get_contents('https://www.econoco.com/niki-female-pose-3-w-oval-head/');
    $regex = '/"data":\s(\[.*?])/';
    preg_match( $regex, $web, $matches );
    $data = explode('"data": ',$matches[0]);
    // $short_product_info = json_decode( "$matches[0]", true, 512, JSON_THROW_ON_ERROR );
    return $data;
    // $data = $matches[0];
    // var_dump($matches[0]);
    // printf($short_product_info);
    // $short_product_info = json_decode( $data, true, 512, JSON_THROW_ON_ERROR );
    // echo gettype($short_product_info);
    // if ( isset( $matches[ 1 ] ) ) {
    //     $images = $matches[ 1 ];

    //     $decodeImage = json_decode( $images, true, 512, JSON_THROW_ON_ERROR );

    //     var_dump($decodeImage);
    // }
    // else
    // {
    //     var_dump($decodeImage);
    // }
?>