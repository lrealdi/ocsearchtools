<?php

class OCCalendarSearch
{
    
    protected $query;
    
    //@todo caricare classe da ini
    public static function instance( $query )
    {
        return new OCCalendarSearch( $query );
    }
    
    protected function __construct( OCCalendarSearchQuery $query )
    {
        $this->query = $query;
    }
    
    // per fare debug
    public function query()
    {
        return $this->query->getRequest();    
    }
    
    // per fare debug
    public function solrData()
    {
        return $this->query->getSolrData(); 
    }
    
    public function facets()
    {
        return $this->query->makeFacets();        
    }
    
    public function result()
    {
        $result = array(
            'current_dates' => $this->query->makeDate(),
            'events' => $this->query->makeEvents(),
            'count' => $this->query->makeEventCount()
        );
        return $result;
    }    
}
