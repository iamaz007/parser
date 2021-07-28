<?php

namespace App\Feeds\Vendors\MOL;

use App\Feeds\Parser\HtmlParser;

class Parser extends HtmlParser
{
    private string $selector = "center table table tr .data";
    private array $short_desc = [];
    private array $imgs = [];

    public function beforeParse(): void
    {
        // get imgaes
        $tmpImgs = array_unique($this->getSrcImages('.price-info a img'));
        if (count($tmpImgs) > 0) {
            $this->imgs = $tmpImgs;
        } else {
            $this->imgs = $this->getSrcImages('#listing_main_image_link img');
        }
        


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
        return $this->imgs;
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

    public function getAvail(): ?int
    {
        return self::DEFAULT_AVAIL_NUMBER;
    }
}