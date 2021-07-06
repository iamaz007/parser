<?php

namespace App\Feeds\Vendors\ECN;

use App\Feeds\Feed\FeedItem;
use App\Feeds\Parser\HtmlParser;
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
        if ( $this->exists( '.tier-price-container ul li span span span' ) ) {
            return $this->getMoney( '.tier-price-container ul li span span span' );
        }
    }

    public function getCostToUs(): float
    {
        if ( $this->exists( '.tier-price-container ul li span span span' ) ) {
            return $this->getMoney( '.tier-price-container ul li span span span' );
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
        return $this->getSrcImages( '.fotorama__nav__frame--thumb .fotorama__thumb img' );
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

        $products = html_entity_decode( $this->getHtml( '.configurable-product__tier-price' ) );

        $products_data = json_decode( $products, true, 512, JSON_THROW_ON_ERROR );

        foreach ( $products_data as $product_data ) {
            $fi = clone $parent_fi;

            $product_name = [];
            foreach ( $product_data[ 'attributes' ] as $attribute ) {
                $product_name[] = ucfirst( $attribute );
            }

            $fi->setMpn( $product_data[ 'sku' ] );
            $fi->setProduct( implode( ' ', $product_name ) );
            $fi->setCostToUs( StringHelper::getMoney( $product_data[ 'display_price' ] ) );
            $fi->setRAvail( $product_data[ 'is_in_stock' ] ? self::DEFAULT_AVAIL_NUMBER : 0 );

            $fi->setDimX( $product_data[ 'dimensions' ][ 'width' ] ?: null );
            $fi->setDimY( $product_data[ 'dimensions' ][ 'height' ] ?: null );
            $fi->setDimZ( $product_data[ 'dimensions' ][ 'length' ] ?: null );

            $fi->setWeight( $product_data[ 'weight' ] ?: null );

            $child[] = $fi;
        }
        return $child;
    }
}
