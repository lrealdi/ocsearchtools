<?php

class OCCalendarSearchQuery
{
    
    protected $request = array();

    public $solrQuery = null;
    
    public $solrParams = array(
        'SearchOffset' => 0,
        'SearchLimit' => 1000,
        'Facet' => array(),
        'SortBy' => array(),
        'Filter' => array(),
        'SearchContentClassID' => null,
        'SearchSectionID' => null,
        'SearchSubTreeArray' => array( 2 ),
        'AsObjects' => false,
        'SpellCheck' => null,
        'IgnoreVisibility' => null,
        'Limitation' => null,
        'BoostFunctions' => null,
        'QueryHandler' => 'ezpublish',
        'EnableElevation' => true,
        'ForceElevation' => true,
        'SearchDate' => null,
        'DistributedSearch' => null,
        'FieldsToReturn' => array(
            'attr_from_time_dt',
            'attr_to_time_dt',                
        ),
        'SearchResultClustering' => null,
        'ExtendedAttributeFilter' => array()
    );
    
    public $solrResult = array();
    
    public static function instance( $query, $defaultParams )
    {
        return new OCCalendarSearchQuery( $query, $defaultParams );
    }
    
    protected function __construct( $query, $defaultParams )
    {
        $this->request = $query;
        $this->parse();
        $this->solrParams = array_merge( $this->solrParams, $defaultParams );
        $this->fetch();
    }
    
    protected function fetch()
    {
        $this->solrParams['SortBy'] = array(            
            'attr_priority_si' => 'desc',
            'attr_special_b' => 'desc'
        );
        
        if ( class_exists( 'ezfIndexEventDuration' ) )
        {
            $this->solrParams['SortBy']['extra_event_duration_s'] = 'asc';    
        }
        
        $this->solrParams['SortBy']['attr_from_time_dt'] = 'asc';
                
        $solrSearch = new OCSolr();
        $this->solrResult = $solrSearch->search( $this->solrQuery, $this->solrParams );
    }
    
    protected function parse()
    {
        if ( isset( $this->request[ 'text' ] ) )
        {
            $this->parseText( $this->request[ 'text' ] ); 
        }
        
        if ( isset( $this->request[ 'when' ] ) )
        {
            $this->parseWhen( $this->request[ 'when' ] );
        }
        
        if ( isset( $this->request[ 'dateRange' ] )
             && isset( $this->request[ 'when' ] )
             && $this->request[ 'when' ] == 'range' )
        {
            $this->parseDateRange( $this->request[ 'dateRange' ] ); 
        }
        
        if ( isset( $this->request[ 'what' ] ) )
        {
            $this->parseWhat( $this->request[ 'what' ] ); 
        }
        
        if ( isset( $this->request[ 'where' ] ) )
        {
            $this->parseWhere( $this->request[ 'where' ] );
        }
        
        if ( isset( $this->request[ 'target' ] ) )
        {
            $this->parseTarget( $this->request[ 'target' ] ); 
        }
        
        if ( isset( $this->request[ 'category' ] ) )
        {
            $this->parseCategory( $this->request[ 'category' ] ); 
        }
    }
    
    protected function parseText( $data )
    {
        $this->solrQuery = $data;
    }
    
    protected function parseWhen( $data )
    {
        switch ( $data )
        {
            case 'today':
            {
                $this->addSolrFilter( self::getSolrDateFilter( new DateTime( 'now' ) ) );
            } break;
            
            case 'tomorrow':
            {
                $this->addSolrFilter( self::getSolrDateFilter( new DateTime( 'tomorrow' ) ) );
            } break;
            
            case 'weekend':
            {
                $this->addSolrFilter( self::getSolrDateFilter( new DateTime( 'next saturday' ), new DateTime( 'next sunday' ) ) );
            } break;
        }
    }
    
    protected function parseDateRange( $data )
    {
        if ( is_array( $data ) && count( $data ) == 2 )
        {
            $start = DateTime::createFromFormat( 'Ymd', $data[0], new DateTimeZone( "Europe/Rome" ) );
            $end = DateTime::createFromFormat( 'Ymd', $data[1], new DateTimeZone( "Europe/Rome" ) );            
            $this->addSolrFilter( self::getSolrDateFilter( $start, $end ) );
        }
    }
    
    protected function parseWhat( $data )
    {
    }
    
    protected function parseWhere( $data )
    {
    }
    
    protected function parseTarget( $data )
    {
    }
    
    protected function parseCategory( $data )
    {
    }
    
    protected function addSolrFilter( $data )
    {
        $this->solrParams['Filter'][] = $data;
    }
    
    protected static function getSolrDateFilter( DateTime $start, DateTime $end = null )
    {
        if ( $end == null )
        {
            $end = clone $start;            
        }
        
        $start->setTime( 0, 0 );
        $end->setTime( 23, 59 );
                
        $startSolr = strftime( '%Y-%m-%dT%H:%M:%SZ', $start->format( 'U' ) ); //ezfSolrDocumentFieldBase::preProcessValue( $start->format( 'U' ), 'date' );
        $endSolr = strftime( '%Y-%m-%dT%H:%M:%SZ', $end->format( 'U' ) ); //ezfSolrDocumentFieldBase::preProcessValue( $end->format( 'U' ) - 1 , 'date' );
        
        return array(
            'or',
            'attr_from_time_dt:[' . $startSolr . ' TO ' . $endSolr . ']',
            'attr_to_time_dt:[' . $startSolr . ' TO ' . $endSolr . ']',
            array(
                'and',
                'attr_from_time_dt:[* TO ' . $startSolr . ']',
                'attr_to_time_dt:[' . $endSolr . ' TO *]'
            )
        );
    }
    
}
