<?php

abstract class OCSearchQuery
{
    public $queryText;

    public $queryParameters;

    abstract public function fetch();

    abstract public function addQueryFilter( $data );

    abstract public function addQueryFacet( $data );
}