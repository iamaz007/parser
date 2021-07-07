<?php

namespace App\Feeds\Vendors\ECN;

use App\Feeds\Feed\FeedItem;
use App\Feeds\Parser\HtmlParser;
use App\Feeds\Utils\ParserCrawler;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\StringHelper;

class Parser extends HtmlParser
{
    public function getMpn(): string
    {
        return $this->getText('.label__item-sku');
    }

    public function getProduct(): string
    {
        return $this->getText('.product-info-title');
    }

    public function getListPrice(): ?float
    {
        if ( $this->exists( '.tier-price-container ul li .price-container span' ) ) {
            return $this->getMoney( '.tier-price-container ul li .price-container span' );
        }
    }

    public function getCostToUs(): float
    {
        if ( $this->exists( '.tier-price-container ul li .price-container span' ) ) {
            return $this->getMoney( '.tier-price-container ul li .price-container span' );
        }
    }

    public function getDescription(): string
    {
        return $this->getHtml('.product .value');
    }

    public function getShortDescription(): array
    {
        if ( $this->exists( '.description .value' ) ) {
            return $this->getContent( '.description .value p' );
        }
        return [];
    }

    public function getImages(): array
    {
        $regex = '/"data":\s(\[.*?])/';
        preg_match( $regex, $this->node->html(), $matches );
        $striped = stripslashes($matches[0]);
        $replacedD = str_replace(['"data": [',']'], '',$striped);
        
        $regex2 = '/"full":(\".*?")/';
        preg_match_all( $regex2, $replacedD, $matches2 );

        $striped2 = [];
        $replacing = ['"full":','\"'];
        $replacer = ["",""];
        for ($i=0; $i < count($matches2[0]); $i++) { 
            $str = stripslashes($matches2[0][$i]);
            $newPhrase = str_replace($replacing, $replacer, $str);
            array_push($striped2, $newPhrase);
        }
        
        return array_unique($striped2);
    }

    public function getAvail(): ?int
    {
        if ( $this->exists( '.check-stock__modal-container' ) ) { 
            $text = $this->getText( '.check-stock__modal-container > p' );
            $arr = explode(" ",$text);
            return $arr[2];
        }
        return 0;
    }

    public function getWeight(): ?float
    {
        return $this->getText( 'tbody tr td[data-th="Weight"]') ?? 0;
    }

    public function getChildProducts(FeedItem $parent_fi): array
    {
        $child = [];

        $this->filter('.configurable-product__tier-price')->each(function (ParserCrawler $c) use ($parent_fi)
        {
            $fi = clone $parent_fi;
            $fi->setMpn( $c->getText('.configurable-product__sku') );
            $fi->setRAvail( self::DEFAULT_AVAIL_NUMBER );
            $fi->setCostToUs($c->getMoney('.tier-price-container ul li .tier-price .price-container .price-wrapper'));

            $child[] = $fi;
        });
        return $child;
    }
}
