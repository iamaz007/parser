<?php

namespace App\Feeds\Vendors\MOL;

use App\Feeds\Processor\HttpProcessor;

class Vendor extends HttpProcessor
{
    // public const CATEGORY_LINK_CSS_SELECTORS = [ '#column1 table .cat', '#frmsortby table tbody tr:nth-child(2) td table tr td table:last-child tbody tr td a' ];
    public const CATEGORY_LINK_CSS_SELECTORS = [ '#column1 table .cat' ];
    public const PRODUCT_LINK_CSS_SELECTORS = [ '#frmsortby table table .alternative a' ];

    protected array $first = [ 'https://www.moriental.com/' ];
}