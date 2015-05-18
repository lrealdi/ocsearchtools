<?php

interface OCCalendarSearchContextInterface
{
    public function identifier();

    public function solrFetchParams();

    public function cacheKey();

    public function parseResults( array $rawResults, DateTime $startDateTime, DateTime $endDateTime = null );

    public function getSolrFilters( array $data, OCCalendarSearchTaxonomy $taxonomy );

    public function taxonomyTree( $taxonomyIdentifier );

}