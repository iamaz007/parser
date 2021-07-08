<?php

namespace App\Feeds\Vendors\ECN;

use App\Feeds\Feed\FeedItem;
use App\Feeds\Processor\HttpProcessor;
use App\Feeds\Utils\Data;
use App\Feeds\Utils\Link;

class Vendor extends HttpProcessor
{
    public const CATEGORY_LINK_CSS_SELECTORS = [ '.navigation-mobile li a' ];
    public const PRODUCT_LINK_CSS_SELECTORS = [ '.product-items a' ];

    protected ?int $max_products = 50;

    protected array $first = [ 'https://www.econoco.com/' ];

   

}
