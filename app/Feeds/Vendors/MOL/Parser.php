<?php

namespace App\Feeds\Vendors\MOL;

use App\Feeds\Parser\HtmlParser;
use App\Helpers\FeedHelper;
use App\Helpers\StringHelper;

class Parser extends HtmlParser
{
    private string $selector = "center table table tr .data";
    private string $title = "";
    private float $cost_to_us = 0;
    private array $short_desc = [];
    private array $imgs = [];
    private array $dims = [];
    private array $categories = [];
    private array $dimensRegex = [
        '/Dimensions: (.*")/',
        '/DIMENSIONS: (.*")/',
        '/Tray Dimensions: (.*")/',
        '/Tiles Dimensions: (.*")/',
        '/Approximate Dimensions: (.*")/',
        '/APPROXIMATE DIMENSIONS: (.*")/',
        '/Size: (.*\))/',
        '/Size Approx: (.*")/',
        '/Dimensions Appox: (.*)/',
        '/Dimensions Approx: (.*)/',
        '/Each Cat Measures Approximately: (.*)/'
    ];
    private string $fullDesc = '';
    private array $attributes = [];

    public function beforeParse(): void
    {
        // get title
        $this->title = $this->getText($this->selector . ' .page_headers');
        preg_match('/\W\$(.*)/',$this->title,$titleMatches);
        if (count($titleMatches) > 0) {
            $this->title = preg_replace('/\W\$(.*)/', '', $this->title);

            // get price
            $tempPrice = explode("$",$titleMatches[0]);
            if (count($tempPrice)) {
                $this->cost_to_us = floatval($tempPrice[0]);
            }
        }

        // get categories
        $tempCat = $this->getContent('center .data table .frame-ht .data table table .item a:nth-child(2)');
        if (count($tempCat) > 0) {
            $this->categories = [array_values($tempCat)[0]];
        }

        // get imgaes
        $tmpImgs = array_unique($this->getLinks('.price-info a'));
        $this->imgs = $tmpImgs;
        array_pop($this->imgs);
        array_pop($this->imgs);
        $tempImgLink = $this->getLinks('#listing_main_image_link');
        if (count($tempImgLink) > 0) {
            foreach ($tempImgLink as $key => $value) {
                array_push($this->imgs,$tempImgLink[$key]);
            }
        }

        // get full desc
        $this->fullDesc = $this->getText('.item .alternative .item');
        $tempFullDesc = $this->getHtml('.item .alternative .item');

        // get short desc
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
        foreach ($this->short_desc as $key => $value) { 
            if (!str_contains($this->short_desc[$key],":")) {
                unset($this->short_desc[$key]);
            }
        }

        // get attr
        $tempFullDescText = preg_split('/<[^>]*>/', $tempFullDesc);
        $trimmed_array = array_map('trim', $tempFullDescText);
        foreach ($trimmed_array as $key => $value) {
            if (strpos($trimmed_array[$key],':') !== false) {
                $tempArr = explode(":",$trimmed_array[$key]);
                if (str_word_count($tempArr[0], 0) <= 3) {
                    if (StringHelper::mb_trim($tempArr[1]) != '') {
                        $this->attributes[StringHelper::mb_trim($tempArr[0])] = StringHelper::mb_trim($tempArr[1]);
                    }
                }
            }
        }

        // get dimensions
        foreach ($this->dimensRegex as $key => $value) {
            preg_match($this->dimensRegex[$key], $this->node->html(), $matches);
            if (count($matches) > 0) {
                $this->short_desc = preg_replace($this->dimensRegex[$key], '', $this->short_desc);
                $this->fullDesc = preg_replace($this->dimensRegex[$key], '', $this->fullDesc);
                if (strpos($matches[1], 'x') !== false ) {
                    $this->dims = FeedHelper::getDimsInString($matches[1], 'x');
                }
                else if (strpos($matches[1], 'X') !== false ) {
                    $this->dims = FeedHelper::getDimsInString($matches[1], 'X');
                }
                else
                {
                    $this->dims = FeedHelper::getDimsInString($matches[1], ',');
                }
            }
        }

        // if attr exist in fullDescription or shortDesc, remove it
        foreach ($this->attributes as $key => $value) {
            $this->fullDesc = str_replace($key.": ".$value,'', $this->fullDesc);
            $this->short_desc = str_replace($key.": ".$value,'', $this->short_desc);
        }
    }

    public function getMpn(): string
    {
        return $this->getText($this->selector . ' #product_id');
    }

    public function getProduct(): string
    {
        return $this->title;
    }

    public function getCostToUs(): float
    {
        return $this->cost_to_us ?: 0;   
    }

    public function getImages(): array
    {
        return $this->imgs;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function getShortDescription(): array
    {
        return $this->short_desc;
    }

    public function getDescription(): string
    {
        return $this->fullDesc;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getAvail(): ?int
    {
        return self::DEFAULT_AVAIL_NUMBER;
    }

    public function getDimX(): ?float
    {
        return $this->dims['x'] ?? null;
    }

    public function getDimY(): ?float
    {
        return $this->dims['y'] ?? null;
    }

    public function getDimZ(): ?float
    {
        return $this->dims['z'] ?? null;
    }
}
