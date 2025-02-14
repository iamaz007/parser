<?php

namespace App\Feeds\Vendors\MOL;

use App\Feeds\Processor\HttpProcessor;

class Vendor extends HttpProcessor
{
    public const CATEGORY_LINK_CSS_SELECTORS = [ '#column1 table .cat', '.font2 a' ];
    public const PRODUCT_LINK_CSS_SELECTORS = [ '#frmsortby table table .alternative a' ];

    protected array $first = [ 'https://www.moriental.com/' ];
}