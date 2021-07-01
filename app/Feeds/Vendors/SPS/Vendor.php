<?php

namespace App\Feeds\Vendors\SPS;

use App\Feeds\Processor\SitemapHttpProcessor;
use App\Feeds\Utils\Link;
use App\Feeds\Utils\Data;
use Symfony\Component\DomCrawler\Crawler;

class Vendor extends SitemapHttpProcessor
{
    protected array $first = [ 'https://www.sportsmith.com/sitemap.xml' ];
    protected ?int $max_products = 20;
    

    public function getProductsLinks( Data $data, string $url ): array
    {
        // return Vendor::productLinkParser();
        $data->setData(str_replace(['<![CDATA[',']]>'], '',$data->getData()));
        return parent::getProductsLinks($data,$url);
    }

    public function getCategoriesLinks( Data $data, string $url ): array
    {
        $data->setData(str_replace(['<![CDATA[',']]>'], '',$data->getData()));
        return parent::getCategoriesLinks($data,$url);
    }

    public static function productLinkParser()
    {
        $array = json_decode(json_encode(simplexml_load_file('https://www.sportsmith.com/productSitemap-3.xml', 'SimpleXMLElement', LIBXML_NOCDATA) ), TRUE);

        $arr= $array['url'];
        $finlArr = [];
        for ($i=0; $i < 10; $i++) { 
            array_push($finlArr,stripslashes($arr[$i]['loc']));
        }
        return $finlArr;
    }
}