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
    private array $attributes = [];
    private array $ship_dims = [];
    private float $weight = 0;

    public function beforeParse(): void
    {
        $this->filter('.additional-attributes-wrapper table tbody tr')->each(function (ParserCrawler $c) {
            $key = $c->filter('td')->getNode(0)->textContent;
            $value = $c->filter('td')->getNode(1)->textContent;

            switch ($key) {
                case 'Carton Dimensions':
                    $this->ship_dims = FeedHelper::getDimsInString($value, 'x');
                    break;
                case 'Weight':
                    $this->weight = (float)$value;
                    break;
                
                default:
                    $this->attributes[$key] = StringHelper::mb_trim($value);
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
        return $this->getHtml('#description .product .value');
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

    public function getAttributes(): ?array
    {
            return $this->attributes ?: null;
    }

    public function getWeight(): ?float
    {
        return $this->weight ?: null;
    }

    public function getShippingDimX(): ?float
    {
        return $this->ship_dims['x'] ?? null;   
    }

    public function getShippingDimY(): ?float
    {
        return $this->ship_dims['y'] ?? null;
    }

    public function getShippingDimZ(): ?float
    {
        return $this->ship_dims['z'] ?? null;
    }

    public function isGroup(): bool
    {
        return $this->exists('.configurable-product__tier-price');
    }

    public function getProductFiles(): array
    {
        $files = [];

        $this->filter('.downloads-item .item-link a')->each(function (ParserCrawler $node) use (&$files)
        {
            $link = $node->attr('href');
            if (!empty($link) && str_contains($link,'http')) {
                $files[] = [
                    'name'=> $node->attr('title') ?: $this->getProduct(),
                    'link'=> $link,
                ];
            }
        });

        return $files;
    }

    public function getChildProducts(FeedItem $parent_fi): array
    {
        $child = [];

        $this->filter('.configurable-product__tier-price')->each(function (ParserCrawler $c) use ($parent_fi, &$child) {
            $fi = clone $parent_fi;
            $fi->setMpn($c->getText('.configurable-product__sku'));
            $fi->setProduct($c->getText('.product-specs-container .additional-attributes-wrapper table tbody tr td[data-th="Holds"]'));
            $fi->setMinAmount((int)($c->getText('.product-specs-container .additional-attributes-wrapper table tbody tr td[data-th="Pieces Per Full Carton"]')));
            $text = $c->getText('.modal .check-stock__modal-container > p');
            $arr = explode(" ", $text);
            $fi->setRAvail($arr[2] ?? 0);

            $fi->setCostToUs($c->getMoney('.price-container .price-wrapper'));

            $child[] = $fi;
        });
        return $child;
    }
}
