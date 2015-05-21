<?php

interface OCCalendarSearchContextInterface
{
    public function identifier();

    public function solrFetchParams();

    public function taxonomiesCacheKey();
    
    public function cacheKey();

    public function parseResults( array $rawResults, DateTime $startDateTime, DateTime $endDateTime = null );

    public function parseFacets( array $rawFacetsFields, array $parsedRequest );

    public function getSolrFilters( array $data, OCCalendarSearchTaxonomy $taxonomy );

    public function taxonomyTree( $taxonomyIdentifier );

}