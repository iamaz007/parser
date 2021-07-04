<?php

namespace App\Feeds\Vendors\ECN;

use App\Feeds\Parser\HtmlParser;
use Symfony\Component\DomCrawler\Crawler;

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
        return $this->getText('.product .value');
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
        return $this->node
            ->filter( '.fotorama__thumb img' )
            ->each( static fn( Crawler $crawler ) => $crawler->attr( 'src' ) );
    }

    public function getCategories(): array
    {
        return [];
    }

    public function getBrand(): ?string
    {
        return '';
    }

    public function getAvail(): ?int
    {
        return self::DEFAULT_AVAIL_NUMBER;
    }


    public function getWeight(): ?float
    {
        return $this->getText( 'tbody tr td[data-th="Weight"]') ?? 0;
    }
}
