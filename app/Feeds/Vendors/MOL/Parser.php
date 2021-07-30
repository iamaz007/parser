<?php

namespace App\Feeds\Vendors\MOL;

use App\Feeds\Parser\HtmlParser;
use App\Helpers\FeedHelper;

class Parser extends HtmlParser
{
    private string $selector = "center table table tr .data";
    private array $short_desc = [];
    private array $imgs = [];
    private array $dims = [];

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

        // get dimensions
        $regex = '/<li>Dimensions: (.*?)<\/li>/';
        preg_match($regex, $this->node->html(), $matches);
        if (count($matches) > 0) {
            if ($matches[1] != '') {
                $this->dims = FeedHelper::getDimsInString($matches[1], 'x');
                preg_replace($regex, '', $this->short_desc);
            }
        }
        else
        {
            $regex = '/Approximate Dimensions: (.*")/';
            preg_match($regex, $this->node->html(), $matches);
            if (count($matches) > 0) {
                if ($matches[1] != '') {
                    $this->dims = FeedHelper::getDimsInString($matches[1], 'x');
                    preg_replace($regex, '', $this->short_desc);
                }
            }
            else
            {
                $regex = '/Size: (.*\))/';
                preg_match($regex, $this->node->html(), $matches);
                if (count($matches) > 0) {
                    if ($matches[1] != '') {
                        $this->dims = FeedHelper::getDimsInString($matches[1], ',');
                        preg_replace($regex, '', $this->short_desc);
                    }
                }
                else
                {
                    $regex = '/Size Approx: (.*")/';
                    preg_match($regex, $this->node->html(), $matches);
                    if (count($matches) > 0) {
                        if ($matches[1] != '') {
                            $this->dims = FeedHelper::getDimsInString($matches[1], 'x');
                            preg_replace($regex, '', $this->short_desc);
                        }
                    }
                    else
                    {
                        $regex = '/<div>Dimensions Appox: (.*)<\/div>/';
                        preg_match($regex, $this->node->html(), $matches);
                        if (count($matches) > 0) {
                            if ($matches[1] != '') {
                                $this->dims = FeedHelper::getDimsInString($matches[1], 'x');
                                preg_replace($regex, '', $this->short_desc);
                            }
                        }
                        {
                            $regex = '/<div>Each Cat Measures Approximately: (.*)<\/div>/';
                            preg_match($regex, $this->node->html(), $matches);
                            if (count($matches) > 0) {
                                if ($matches[1] != '') {
                                    $this->dims = FeedHelper::getDimsInString($matches[1], 'x');
                                    preg_replace($regex, '', $this->short_desc);
                                }
                            }
                        }
                    }
                }
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