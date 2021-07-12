<?php

namespace App\Feeds\Vendors\ECN;

use App\Feeds\Feed\FeedItem;
use App\Feeds\Parser\HtmlParser;
use App\Feeds\Utils\ParserCrawler;
use App\Helpers\FeedHelper;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\StringHelper;

class Parser extends HtmlParser
{

    public function beforeParse(): void
    {
        $this->filter('.additional-attributes-wrapper table tbody tr')->each(function (ParserCrawler $c) {
            $key = $c->filter('td')->getNode(0)->textContent;
            $value = $c->filter('td')->getNode(1)->textContent;

            switch ($key) {
                // case 'Dimensions':

                //     break;
                case 'Carton Dimensions':
                    $this->ship_dims = FeedHelper::getDimsInString($value, 'x');
                    break;
                case 'Weight':
                    $this->weight = (float)$value;
                    break;
                
                default:
                    $this->attributes[$key] = $value;
                    break;
            }
        });
        
    }

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
        if ($this->exists('.tier-price-container ul li .price-container span')) {
            return $this->getMoney('.tier-price-container ul li .price-container span');
        }
    }

    public function getCostToUs(): float
    {
        if ($this->exists('.tier-price-container ul li .price-container span')) {
            return $this->getMoney('.tier-price-container ul li .price-container span');
        }
    }

    public function getDescription(): string
    {
        return $this->getHtml('.product .value');
    }

    public function getImages(): array
    {
        $regex = '/"data":\s\[(.*?)]/';
        preg_match($regex, $this->node->html(), $matches);
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

    public function getAvail(): ?int
    {
        if ($this->exists('.check-stock__modal-container')) {
            $text = $this->getText('.check-stock__modal-container > p');
            $arr = explode(" ", $text);
            return $arr[2];
        }
        return 0;
    }

    // public function getAttributes(): ?array
    // {
    //     $child = [];
    //     $this->filter('.additional-attributes-wrapper table tbody tr')->each(function (ParserCrawler $c) use (&$child) {
    //         if ($c->filter('td')->getNode(0)->textContent != 'Carton Dimensions' && $c->filter('td')->getNode(0)->textContent != 'Weight') {
    //             $child[ $c->filter('td')->getNode(0)->textContent ] = StringHelper::mb_trim($c->filter('td')->getNode(1)->textContent);
    //         }
    //     });
    //     return $child;
    // }

    // public function getWeight(): ?float
    // {
    //     return $this->getText('tbody tr td[data-th="Weight"]') ?? 0;
    // }

    // public function getDimX(): ?float
    // {
    //     $arr = explode( 'x', $this->getText('tbody tr td[data-th="Carton Dimensions"]'));
    //     if (array_key_exists(0,$arr)) {
    //         return StringHelper::getFloat($arr[0]);
    //     } else {
    //         return 0;
    //     }
        
    // }

    // public function getDimY(): ?float
    // {
    //     $arr = explode( 'x', $this->getText('tbody tr td[data-th="Carton Dimensions"]'));
    //     if (array_key_exists(1,$arr)) {
    //         return StringHelper::getFloat($arr[1]);
    //     } else {
    //         return 0;
    //     }
    // }

    // public function getDimZ(): ?float
    // {
    //     $arr = explode( 'x', $this->getText('tbody tr td[data-th="Carton Dimensions"]'));
    //     if (array_key_exists(2,$arr)) {
    //         return StringHelper::getFloat($arr[2]);
    //     } else {
    //         return 0;
    //     }
    // }

    

    public function isGroup(): bool
    {
        return $this->exists('.configurable-product__tier-price');
    }

    public function getChildProducts(FeedItem $parent_fi): array
    {
        $child = [];

        $this->filter('.configurable-product__tier-price')->each(function (ParserCrawler $c) use ($parent_fi, &$child) {
            $fi = clone $parent_fi;
            $fi->setMpn($c->getText('.configurable-product__sku'));

            $text = $c->getText('.modal .check-stock__modal-container > p');
            $arr = explode(" ", $text);
            $fi->setRAvail($arr[2] ?? self::DEFAULT_AVAIL_NUMBER);

            $fi->setCostToUs($c->getMoney('.price-container .price-wrapper'));

            $child[] = $fi;
        });
        return $child;
    }
}
