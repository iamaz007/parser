<?php

namespace App\Feeds\Vendors\MOL;

use App\Feeds\Parser\HtmlParser;
use App\Helpers\FeedHelper;
use App\Helpers\StringHelper;

class Parser extends HtmlParser
{
    private string $selector = "center table table tr .data";
    private array $short_desc = [];
    private array $imgs = [];
    private array $dims = [];
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
        '/Each Cat Measures Approximately: (.*)/'
    ];
    private string $fullDesc = '';
    private array $attributes = [];

    public function beforeParse(): void
    {
        // get imgaes
        $tmpImgs = array_unique($this->getLinks('.price-info a'));
        $this->imgs = $tmpImgs;
        array_pop($this->imgs);
        array_pop($this->imgs);
        if (count($this->imgs) <= 0) {
            $this->imgs = $this->getSrcImages('#listing_main_image_link img');
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

        // get attr
        $tempFullDescText = preg_split('/<[^>]*>/', $tempFullDesc);
        $trimmed_array = array_map('trim', $tempFullDescText);
        for ($i=0; $i < count($trimmed_array); $i++) { 
            if (strpos($trimmed_array[$i],':') !== false) {
                $tempArr = explode(":",$trimmed_array[$i]);
                $this->attributes[StringHelper::mb_trim($tempArr[0])] = StringHelper::mb_trim($tempArr[1]);
            }
        }

        // get dimensions
        for ($i=0; $i < count($this->dimensRegex); $i++) { 
            preg_match($this->dimensRegex[$i], $this->node->html(), $matches);
            if (count($matches) > 0) {
                $this->short_desc = preg_replace($this->dimensRegex[$i], '', $this->short_desc);
                $this->fullDesc = preg_replace($this->dimensRegex[$i], '', $this->fullDesc);
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
    }

    public function getMpn(): string
    {
        return $this->getText($this->selector . ' #product_id');
    }

    public function getProduct(): string
    {
        return $this->getText($this->selector . ' .page_headers');
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
        $arr = $this->getContent('center .data table .frame-ht .data table table .item a:nth-child(2)');
        return [array_values($arr)[0]];
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
