<?php

namespace App\Feeds\Vendors\MOL;

use App\Feeds\Parser\HtmlParser;
use App\Feeds\Utils\ParserCrawler;
use App\Helpers\StringHelper;

class Parser extends HtmlParser
{
    private string $url = 'https://www.moriental.com/';
    private string $selector = "center table table tr .data";
    private array $imgs = [];
    private array $short_desc = [];

    public function beforeParse(): void
    {
        // get desc
        $tempShortDesc = $this->getContent('.frame-ht table[width="98%"] .item .alternative .item span');
        if (count($tempShortDesc) > 0) {
            $this->short_desc = $tempShortDesc;
        } else {
            $tempShortDesc = $this->getContent('.frame-ht table[width="98%"] .item .alternative .item div');
            if (count($tempShortDesc) > 0) {
                $this->short_desc = $tempShortDesc;
            } else {
                $this->short_desc = $this->getContent('.item li');
            }
            
        }
        
    }
    public function getMpn(): string
    {
        return $this->getText($this->selector.' #product_id');
    }

    public function getProduct(): string
    {
        return $this->getText($this->selector.' .page_headers');
    }

    public function getImages(): array
    {
        // return $this->imgs;
        return $this->getSrcImages('.price-info a img');
    }

    public function getShortDescription(): array
    {
        return $this->short_desc;
    }

    public function getCategories(): array
    {
        $arr = $this->getContent('center .data table .frame-ht .data table table .item a:nth-child(2)');
        return [array_values($arr)[0]];
    }
}