<?php

namespace App\Feeds\Vendors\MOL;

use App\Feeds\Parser\HtmlParser;
use App\Helpers\FeedHelper;
use App\Helpers\StringHelper;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class Parser extends HtmlParser
{
    private string $selector = "center table table tr .data";
    private string $title = "";
    private float $cost_to_us = 0;
    private array $short_desc = [];
    private array $imgs = [];
    private array $dims = [];
    private array $categories = [];
    private array $shortDescRegex = [
        '/(<li[^>]*>)(.*?)(<\\/li>)/',
        '/(<span[^>]*>)(.*?)(<\\/span>)/',
    ];
    private array $dimensRegex = [
        '/Dimensions: (.*")/',
        '/DIMENSIONS: (.*")/',
        '/Tray Dimensions: (.*")/',
        '/Tray dimensions: (.*")/',
        '/Tiles Dimensions: (.*")/',
        '/Approximate Dimensions: (.*")/',
        '/APPROXIMATE DIMENSIONS: (.*")/',
        '/Size: (.*\))/',
        '/Size Approx: (.*")/',
        '/Dimensions Appox: (.*)/',
        '/Dimensions Approx: (.*)/',
        '/Each Cat Measures Approximately: (.*)/',
        '/Diameter: (.*)/',
        '/\d+.\d+"...x (.*) H/'
    ];
    private array $dimensRegexWithOutColon = [
        '/Dimensions (.*")/',
        '/DIMENSIONS (.*")/',
        '/Tray Dimensions (.*")/',
        '/Tray dimensions (.*")/',
        '/Tiles Dimensions (.*")/',
        '/Approximate Dimensions (.*")/',
        '/APPROXIMATE DIMENSIONS (.*")/',
        '/Size (.*\))/',
        '/Size Approx (.*")/',
        '/Dimensions Appox (.*)/',
        '/Dimensions Approx (.*)/',
        '/Each Cat Measures Approximately (.*)/',
        '/Diameter (.*)/',
        '/\d+.\d+"...x (.*) H/'
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

        // get images
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
        $this->fullDesc = $this->getHtml('.item .alternative .item');
        $this->fullDesc = preg_replace('/<span [^>]+>[^:]*<\/span>/','', $this->fullDesc); //removing short_desc
        
        // $tempFullDesc = preg_replace('/<ul.(.*)>(.*)<\/ul>/ms','', $fullDesc);
        
        
        // get short desc
        $this->short_desc = $this->getContent('.item li');
        

        // get attr
        $tempFullDescText = preg_split('/<[^>]*>/', $this->fullDesc);
        $trimmed_array = array_map('trim', $tempFullDescText);
        foreach ($trimmed_array as $key => $value) {
            if (strpos($trimmed_array[$key],':') !== false && !$this->checkDimRegex($trimmed_array[$key])) {
                $tempArr = explode(":",$trimmed_array[$key]);
                if (str_word_count($tempArr[0], 0) <= 3 && str_word_count($tempArr[1], 0) <= 8) {
                    if (StringHelper::mb_trim($tempArr[1]) != '') {
                        $this->attributes[StringHelper::mb_trim($tempArr[0])] = StringHelper::mb_trim($tempArr[1]);
                    }

                    // if string goes in attributes, then remove it from fullDesc
                    $this->fullDesc = str_replace(StringHelper::mb_trim($tempArr[0]),"",$this->fullDesc);
                    $this->fullDesc = str_replace(":","",$this->fullDesc);
                    $this->fullDesc = str_replace(StringHelper::mb_trim($tempArr[1]),"",$this->fullDesc);
                }
                

                $this->short_desc = str_replace(StringHelper::mb_trim($tempArr[0]),"",$this->short_desc);
                $this->short_desc = str_replace(":","",$this->short_desc);
                $this->short_desc = str_replace(StringHelper::mb_trim($tempArr[1]),"",$this->short_desc);
            }
        }

        // get dimensions
        foreach ($this->dimensRegex as $key => $value) {
            preg_match($this->dimensRegex[$key], $this->node->html(), $matches);
            if (count($matches) > 0) {
                $this->short_desc = preg_replace($this->dimensRegex[$key], '', $this->short_desc);
                $this->short_desc = preg_replace($this->dimensRegexWithOutColon[$key], '', $this->short_desc);
                $this->fullDesc = preg_replace($this->dimensRegex[$key], '', $this->fullDesc);
                if (strpos($matches[1], 'x') !== false ) {
                    $this->dims = FeedHelper::getDimsInString($matches[1], 'x');
                    if ($this->dims['x'] == null || $this->dims['y'] == null || $this->dims['z'] == null) {
                        $this->dims = FeedHelper::getDimsInString($matches[0], 'x');
                    }
                }
                else if (strpos($matches[1], 'X') !== false ) {
                    $this->dims = FeedHelper::getDimsInString($matches[1], 'X');
                }
                else
                {
                    $this->dims = FeedHelper::getDimsInString($matches[1], ',');
                }
                break;
            }
        }

        // removing shortDesc from fullDesc if any
        // $this->fullDesc = preg_replace('/<li (.*)>[^:]*li>/','', $this->fullDesc);
        
        // foreach ($this->short_desc as $key => $value) {
        //     $this->fullDesc = str_replace($this->short_desc[$key],'', $this->fullDesc);
        // }
        $this->fullDesc = preg_replace('/<li [^>]+><\/li>/','', $this->fullDesc);
        $this->fullDesc = preg_replace('/<ul [^>]+><\/ul>/','', $this->fullDesc);
        $this->fullDesc = preg_replace('/<br [^>]+>/','', $this->fullDesc);

        if (empty($this->fullDesc)) {
            $this->fullDesc = $this->getHtml('.item .alternative .item span');
        }
    }

    public function checkDimRegex($str)
    {
        $regexExist = false; 
        foreach ($this->dimensRegex as $key => $value) {
            preg_match($this->dimensRegex[$key], $str, $output_array);
            if (count($output_array) > 0) {
                $regexExist = true;
                break;
            }
        }

        return $regexExist;
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
        return array_unique($this->imgs);
    }

    public function getAttributes(): ?array
    {
        return count($this->attributes) > 0 ? $this->attributes : null;
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
