<?php

class OCCalendarSearch
{
    
    protected $queryHandler;
    
    final public static function instance( $query )
    {
        return new OCCalendarSearch( $query );
    }
    
    protected function __construct( OCCalendarSearchQuery $query )
    {
        $this->queryHandler = $query;
    }
    
    // per fare debug
    public function query()
    {
        return $this->queryHandler->getRequest();
    }
    
    // per fare debug
    public function solrData()
    {
        return $this->queryHandler->getSolrData();
    }
    
    public function facets()
    {
        return $this->queryHandler->makeFacets();
    }
    
    public function result()
    {
        $result = array(
            'current_dates' => $this->queryHandler->makeDate(),
            'events' => $this->queryHandler->makeEvents(),
            'count' => $this->queryHandler->makeEventCount()
        );
        return $result;
    }    
}
