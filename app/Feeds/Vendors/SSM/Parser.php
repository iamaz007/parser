<?php

namespace App\Feeds\Vendors\SSM;

use App\Feeds\Parser\HtmlParser;

class Parser extends HtmlParser
{
    private array $short_product_info = [];

    public function beforeParse(): void
    {
        preg_match( '/application\/ld\+json">(.*)<\//', $this->node->html(), $matches );
        if ( isset( $matches[ 1 ] ) ) {
            $product_info = $matches[ 1 ];

            $this->short_product_info = json_decode( $product_info, true, 512, JSON_THROW_ON_ERROR );
        }
    }

    public function getProduct(): string
    {
        return $this->short_product_info[ 'name' ] ?? '';
    }


    public function getImages(): array
    {
        $img = [];
        if (isset($this->short_product_info[ 'image' ])) {
            array_push($img,$this->short_product_info[ 'image' ]);
        }
        return $img;
    }

    public function getAvail(): ?int
    {
        return self::DEFAULT_AVAIL_NUMBER;
    }


    public function getMpn(): string
    {
        return $this->short_product_info[ 'offers' ][0]['itemOffered']['productID'] ?? '';
    }

    public function getCostToUs(): float
    {
        if ( $this->isGroup() ) {
            return 0;
        }
        return $this->short_product_info[ 'offers' ][ 0 ][ 'price' ] ?? 0;
    }

    public function getBrand(): ?string
    {
        return $this->short_product_info[ 'brand' ] ?? '';
    }
}
