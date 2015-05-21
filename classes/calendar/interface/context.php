<?php

interface OCCalendarSearchContextInterface
{
    public function getIdentifier();

    public function getCacheKey();

    public function getTaxonomiesCacheKey();

    public function setRequest( OCCalendarSearchRequest $request );

    public function setQueryHandler( OCSearchQuery $query );

    public function getData();

    public function parseResults( $results );

    public function parseFacets( $facets );

    public function getTaxonomyTree( $taxonomyIdentifier );

}