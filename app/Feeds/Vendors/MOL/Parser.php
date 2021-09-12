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
        '/Tray Size: (.*")/',
        '/Dimensions: (.*")/',
        '/DIMENSIONS: (.*")/',
        '/Tray Dimensions: (.*")/',
        '/Tray dimensions: (.*")/',
        '/Tiles Dimensions: (.*")/',
        '/Approximate Dimensions: (.*")/',
        '/APPROXIMATE DIMENSIONS: (.*")/',
        '/Size: (.*\))/',
        '/Size: (.*)/',
        '/Size:(.*)/',
        '/Size Approx: (.*")/',
        '/Dimensions Appox: (.*)/',
        '/Dimensions Approx: (.*)/',
        '/Each Cat Measures Approximately: (.*)/',
        '/Measures approximately: (.*)/',
        '/Size Approxly: (.*)/',
        '/\d+.\d+"...x (.*) H/',
        '/L\d\d" X H\d\d"/',
    ];
    private array $dimensRegexWithOutColon = [
        '/Tray Size (.*")/',
        '/Dimensions (.*")/',
        '/DIMENSIONS (.*")/',
        '/Tray Dimensions (.*")/',
        '/Tray dimensions (.*")/',
        '/Tiles Dimensions (.*")/',
        '/Approximate Dimensions (.*")/',
        '/APPROXIMATE DIMENSIONS (.*")/',
        '/Size (.*\))/',
        '/Size (.*)/',
        '/Size(.*)/',
        '/Size Approx (.*")/',
        '/Dimensions Appox (.*)/',
        '/Dimensions Approx (.*)/',
        '/Each Cat Measures Approximately (.*)/',
        '/Measures approximately (.*)/',
        '/Size Approxly (.*)/',
        '/\d+.\d+"...x (.*) H/',
        '/L\d\d" X H\d\d"/',
    ];
    private string $fullDesc = '';
    private array $attributes = [];

    public function beforeParse(): void
    {
        // get title
        $this->title = $this->getText($this->selector . ' .page_headers');
        preg_match('/\W\$(.*)/', $this->title, $titleMatches);
        if (count($titleMatches) > 0) {
            $this->title = preg_replace('/\W\$(.*)/', '', $this->title);

            // get price
            $tempPrice = explode("$", $titleMatches[0]);
            if (count($tempPrice)) {
                $this->cost_to_us = floatval($tempPrice[0]);
            }
        }
        // sizes from the name, transferred to dims
        preg_match('/ \(L \d+.\d+" x W \d.\d"\)/', $this->title, $matches);
        if (count($matches) > 0) {
            $this->dims = FeedHelper::getDimsInString($matches[0], 'x');
            $this->title = preg_replace('/ \(L \d+.\d+" x W \d.\d"\)/', '', $this->title);
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
                array_push($this->imgs, $tempImgLink[$key]);
            }
        }

        // get full desc
        $this->fullDesc = $this->getHtml('.item .alternative .item');
        

        // $tempFullDesc = preg_replace('/<ul.(.*)>(.*)<\/ul>/ms','', $fullDesc);


        // get short desc
        $this->short_desc = $this->getContent('.item li');


        // get attr
        $tempFullDescText = preg_split('/<[^>]*>/', $this->fullDesc);
        $trimmed_array = array_map('trim', $tempFullDescText);
        foreach ($trimmed_array as $key => $value) {
            if (strpos($trimmed_array[$key], ':') !== false && !$this->checkDimRegex($trimmed_array[$key])) {
                
                // else {
                    $tempArr = explode(":", $trimmed_array[$key]);
                    if (str_word_count($tempArr[0], 0) <= 3 && str_word_count($tempArr[1], 0) <= 8) {
                        if (StringHelper::mb_trim($tempArr[1]) != '') {
                            $this->attributes[StringHelper::mb_trim($tempArr[0])] = StringHelper::mb_trim($tempArr[1]);
                        }

                        // if string goes in attributes, then remove it from fullDesc
                        $this->fullDesc = str_replace(StringHelper::mb_trim($tempArr[0]), "", $this->fullDesc);
                        $this->fullDesc = str_replace(":", "", $this->fullDesc);
                        $this->fullDesc = str_replace(StringHelper::mb_trim($tempArr[1]), "", $this->fullDesc);
                    }


                    $this->short_desc = str_replace(StringHelper::mb_trim($tempArr[0]), "", $this->short_desc);
                    $this->short_desc = str_replace(":", "", $this->short_desc);
                    $this->short_desc = str_replace(StringHelper::mb_trim($tempArr[1]), "", $this->short_desc);
                // }
                
            }
        }
        // $this->fullDesc = preg_replace('/<span [^>]+>[^:]*<\/span>/', '', $this->fullDesc); //removing short_desc - 1
        $this->fullDesc = preg_replace('/<li [^>]+><span [^>]+>[^:]*<\/span><\/li>/', '', $this->fullDesc); //removing short_desc

        // get dimensions
        foreach ($this->dimensRegex as $key => $value) {
            preg_match($this->dimensRegex[$key], $this->node->html(), $matches);
            if (count($matches) > 0) {
                $this->short_desc = preg_replace($this->dimensRegex[$key], '', $this->short_desc);
                $this->short_desc = preg_replace($this->dimensRegexWithOutColon[$key], '', $this->short_desc);
                $this->fullDesc = preg_replace($this->dimensRegex[$key], '', $this->fullDesc);

                if (strpos($matches[0], ' x ') !== false) {
                    $this->dims = FeedHelper::getDimsInString($matches[0], ' x ');
                    if (is_null($this->dims['x'])) {
                        $this->dims = FeedHelper::getDimsInString($matches[1], ' x ');
                    }
                } else if (strpos($matches[0], ' X ') !== false) {
                    $this->dims = FeedHelper::getDimsInString($matches[0], ' X ');
                    if (is_null($this->dims['x'])) {
                        $this->dims = FeedHelper::getDimsInString($matches[1], ' X ');
                    }
                } else {
                    $this->dims = FeedHelper::getDimsInString($matches[0], ',');
                    if (is_null($this->dims['x'])) {
                        $this->dims = FeedHelper::getDimsInString($matches[1], ',');
                    }
                }
                break;
            }
        }

        // removing shortDesc from fullDesc if any
        // $this->fullDesc = preg_replace('/<li (.*)>[^:]*li>/','', $this->fullDesc);

        
        $this->fullDesc = preg_replace('/<li [^>]+><\/li>/', '', $this->fullDesc);
        $this->fullDesc = preg_replace('/<ul [^>]+><\/ul>/', '', $this->fullDesc);
        $this->fullDesc = preg_replace('/<br [^>]+>/', '', $this->fullDesc);

        // words are headings, they need to be wrapped in a <p> tag
        preg_match_all('/<span[^>]*>(.*?)<\/span>/', $this->fullDesc, $tempData);
        if (array_key_exists(1, $tempData)) {
            foreach ($tempData as $key => $value) {
                foreach ($tempData[$key] as $key2 => $value2) {
                    if (str_contains($tempData[$key][$key2], ":")) {
                        $tempArray = explode(":", $tempData[$key][$key2]);
                        $this->fullDesc = str_replace($tempArray[0], "<p>" . $tempArray[0] . "</p>", $this->fullDesc);
                    }
                }
            }
        }

        foreach ($this->short_desc as $key => $value) {
            preg_match('/SIZE:.+"/', $this->short_desc[$key], $output_array);
            if (count($output_array) > 0) {
                // array_push($this->attributes,$output_array[0]);
                // SIZE:.+x.+"
                preg_match('/SIZE:.+x.+""/', $output_array[0], $output_array1);
                if(count($output_array1) > 0)
                {
                    $this->dims = FeedHelper::getDimsInString($output_array1[0], 'x');
                }
                else
                {
                    $this->attributes["SIZE"] = StringHelper::mb_trim(substr($output_array[0], strpos($output_array[0], "SIZE:") + 5));
                }
                
            }
        }
        
        $this->short_desc = preg_replace('/SIZE:.+"/','',$this->short_desc);
        $this->fullDesc = $this->removeHtmlAttributesFromDesc($this->fullDesc);

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

    public function removeHtmlAttributesFromDesc($desc)
    {
        $desc = str_replace("Approximate ,",'',$desc);
        $desc = str_replace("Approximate  (overall)",'',$desc);
        $desc = str_replace("Arial",'',$desc);
        $desc = str_replace("sans-serif;",'',$desc);
        $desc = preg_replace('/font-size: (.*?);/','',$desc);
        $desc = preg_replace('/background-color: (.*?);/','',$desc);

        return $desc;
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
