<?php

namespace App\Feeds\Vendors\ECN;

use App\Feeds\Processor\HttpProcessor;

class Vendor extends HttpProcessor
{
    public const CATEGORY_LINK_CSS_SELECTORS = [ '.navigation-mobile li a' ];
    public const PRODUCT_LINK_CSS_SELECTORS = [ '.product-items a' ];

    protected array $first = [ 'https://www.econoco.com/' ];

}
