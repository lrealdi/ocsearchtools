<?php

class OCCalendarSearchQuery
{
    const CACHE_IDENTIFIER = 'calendarquery';
    
    protected $request = array();
    
    protected $context;

    protected $start;

    protected $end;

    public $solrQuery = null;
    
    public $solrFetchParams = array(
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
            'attr_to_time_dt'               
        ),
        'SearchResultClustering' => null,
        'ExtendedAttributeFilter' => array()
    );
    
    public $solrResult = array();
    
    public static function instance( $query, $contextIdentifier )
    {
        return new OCCalendarSearchQuery( $query, $contextIdentifier );
    }
    
    protected function __construct( $query, $contextIdentifier )
    {
        $this->request = $query;        
        $this->context = OCCalendarSearchContext::instance( $contextIdentifier );        
        $this->parse();
        $this->solrFetchParams = array_merge( $this->solrFetchParams, $this->context->solrFetchParams() );
        $this->fetch();
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function getSolrData()
    {
        return array(
            'facet_fields' => $this->solrResult['FacetFields'],
            'params' => $this->solrFetchParams,
            'result' => $this->solrResult            
        );
    }
    
    protected function fetch()
    {
        $this->solrFetchParams['SortBy'] = array(            
            'attr_priority_si' => 'desc',
            'attr_special_b' => 'desc'
        );
        
        if ( class_exists( 'ezfIndexEventDuration' ) )
        {
            $this->solrFetchParams['SortBy']['extra_event_duration_s'] = 'asc';    
        }
        
        $this->solrFetchParams['SortBy']['attr_from_time_dt'] = 'asc';
        
        $parameters = array(
            'solrQuery' => $this->solrQuery,
            'solrFetchParams' => $this->solrFetchParams
        );        
        $currentSiteAccess = $GLOBALS['eZCurrentAccess']['name'];
        $copyParameters = $parameters;
        array_multisort( $copyParameters );
        $cacheFileName = $this->context->cacheKey() . '_' . md5( json_encode( $copyParameters ) ) . '.cache';
        $cacheFilePath = eZDir::path( array( eZSys::cacheDirectory(), self::cacheDirectory(), $currentSiteAccess, $cacheFileName ) );        
        $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
                
        $this->solrResult = $cacheFile->processCache(
            array( 'OCCalendarSearchQuery', 'retrieveCache' ),
            array( 'OCCalendarSearchQuery', 'generateCache' ),
            null,
            null,
            compact( 'parameters' )
        );        
    }
    
    public static function retrieveCache( $file, $mtime, $args )
    {
        $result = include( $file );
        return $result;
    }
    
    public static function generateCache( $file, $args )
    {
        extract( $args );                
        $solrSearch = new OCSolr();
        $result = $solrSearch->search( $parameters['solrQuery'], $parameters['solrFetchParams'] );        
        $result['FacetFields'] = $result['SearchExtras']->attribute( 'facet_fields' );
        unset( $result['SearchExtras'] );
        return array( 'content' => $result,
                      'scope'   => self::CACHE_IDENTIFIER );
    }
    
    public static function clearCache()
    {        
        eZDebug::writeNotice( "Clear calendar query cache", __METHOD__ );
        $siteAccesses = array();
        $ini = eZINI::instance();
        if ( $ini->hasVariable( 'SiteAccessSettings', 'RelatedSiteAccessList' ) &&
             $relatedSiteAccessList = $ini->variable( 'SiteAccessSettings', 'RelatedSiteAccessList' ) )
        {
            if ( !is_array( $relatedSiteAccessList ) )
            {
                $relatedSiteAccessList = array( $relatedSiteAccessList );
            }
            $relatedSiteAccessList[] = $GLOBALS['eZCurrentAccess']['name'];
            $siteAccesses = array_unique( $relatedSiteAccessList );
        }
        else
        {
            $siteAccesses = $ini->variable( 'SiteAccessSettings', 'AvailableSiteAccessList' );
        } 

        $cacheBaseDir = eZDir::path( array( eZSys::cacheDirectory(), self::cacheDirectory() ) );                
        $fileHandler = eZClusterFileHandler::instance();
        $fileHandler->fileDeleteByDirList( $siteAccesses, $cacheBaseDir, '' );        

        $fileHandler = eZClusterFileHandler::instance( $cacheBaseDir );
        $fileHandler->purge();        
    }
    
    public static function cacheDirectory()
    {
        $siteINI = eZINI::instance();
        $items = (array) $siteINI->variable( 'Cache', 'CacheItems' );
        if ( in_array( self::CACHE_IDENTIFIER, $items ) &&  $siteINI->hasGroup( 'Cache_' . self::CACHE_IDENTIFIER ))
        {
            $settings = $siteINI->group( 'Cache_' . self::CACHE_IDENTIFIER );
            if ( isset( $settings['path'] ) )
            {
                return $settings['path'];
            }
        }
        return self::CACHE_IDENTIFIER;
    }
    
    protected function parse()
    {
        if ( isset( $this->request[ 'text' ] ) )
        {            
            $this->solrQuery = $this->request[ 'text' ];
        }
        
        if ( isset( $this->request[ 'when' ] ) )
        {            
            switch ( $this->request[ 'when' ] )
            {
                case 'today':
                {
                    $this->addSolrFilter( $this->getSolrDateFilter( new DateTime( 'now' ) ) );
                } break;
                
                case 'tomorrow':
                {
                    $this->addSolrFilter( $this->getSolrDateFilter( new DateTime( 'tomorrow' ) ) );
                } break;
                
                case 'weekend':
                {
                    $currentDate = new DateTime( 'now' );
                    if ( $currentDate->format('N') >= 6 )
                    {
                        $start = clone $currentDate;
                    }
                    else
                    {
                        $start = new DateTime( 'next saturday' );
                    }
                    $end = clone $start;
                    $end->add(new DateInterval( 'P1D' ) );
                    $this->addSolrFilter( $this->getSolrDateFilter( $start, $end ) );
                } break;
            }
        }
        
        if ( isset( $this->request[ 'dateRange' ] )
             && isset( $this->request[ 'when' ] )
             && $this->request[ 'when' ] == 'range' )
        {            
            if ( is_array( $this->request[ 'dateRange' ] ) && count( $this->request[ 'dateRange' ] ) == 2 )
            {
                $start = DateTime::createFromFormat( 'Ymd', $this->request[ 'dateRange' ][0], new DateTimeZone( "Europe/Rome" ) );
                $end = DateTime::createFromFormat( 'Ymd', $this->request[ 'dateRange' ][1], new DateTimeZone( "Europe/Rome" ) );            
                $this->addSolrFilter( $this->getSolrDateFilter( $start, $end ) );
            }
        }
        
        if ( isset( $this->request[ 'what' ] ) )
        {
            $this->parseTaxonomy( $this->request[ 'what' ], 'what' ); 
        }
        
        if ( isset( $this->request[ 'where' ] ) )
        {
            $this->parseTaxonomy( $this->request[ 'where' ], 'where' );
        }
        
        if ( isset( $this->request[ 'target' ] ) )
        {
            $this->parseTaxonomy( $this->request[ 'target' ], 'target' );
        }
        
        if ( isset( $this->request[ 'category' ] ) )
        {
            $this->parseTaxonomy( $this->request[ 'category' ], 'category' );
        }
    }
    
    protected function parseTaxonomy( $data, $taxonomyIdentifier )
    {        
        $taxonomy = OCCalendarSearchTaxonomy::instance( $taxonomyIdentifier, $this->context );
        $this->addSolrFilter( $taxonomy->getSolrFilters( $data ) );
    }
    
    protected function getSolrDateFilter( DateTime $start, DateTime $end = null )
    {
        $this->start = $start;
        $this->end = $end;
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
    
    protected function addSolrFilter( $data )
    {
        if ( $data )
        {
            $this->solrFetchParams['Filter'][] = $data;
        }
    }
    
    protected function addSolrFacet( $data )
    {
        if ( $data )
        {
            $this->solrFetchParams['Facet'][] = $data;
        }
    }
    
    public function makeFacetItem( $currentItem, $currentFacetsResult )
    {
        $facetItem = false;
        if ( array_key_exists( $currentItem['id'], $currentFacetsResult ) )
        {
            $facetItem = $currentItem;            
            $facetItem['count'] = $currentFacetsResult[$currentItem['id']];
            $facetItem['is_selectable'] = 1;
            $facetItem['children'] = array();
            if ( $currentItem['children'] > 0 )
            {
                foreach( $currentItem['children'] as $child )
                {
                    $childItem = $this->makeFacetItem( $child, $currentFacetsResult );
                    if ( $childItem )
                    {
                        $facetItem['children'][] = $childItem;
                    }
                }
            }
        }
        if ( $currentItem['children'] > 0 )
        {
            $foundChildren = array();
            foreach( $currentItem['children'] as $child )
            {                
                $childItem = $this->makeFacetItem( $child, $currentFacetsResult );
                if ( $childItem )
                {                    
                    $foundChildren[] = $childItem;
                }
            }
            if ( count( $foundChildren ) > 0 )
            {
                $facetItem = $currentItem;
                $facetItem['children'] = $foundChildren;
                $facetItem['is_selectable'] = 1;
            }
        }
        return $facetItem;
    }
    
    public function makeFacets()
    {
        $resultFacets = $this->solrResult['FacetFields'];
        $currentFacetsResult = array();
        foreach( $resultFacets as $resultFacetGroup )
        {
            $currentFacetsResult = $currentFacetsResult + $resultFacetGroup['countList'];
        }
        $facets = array();        
        $taxonomyIdentifiers = array( 'what', 'where', 'target', 'category' );
        $forceTaxonomyIdentifiers = array( 'target', 'category' );
        foreach( $taxonomyIdentifiers as $taxonomyIdentifier )
        {
            $taxonomy = $taxonomy = OCCalendarSearchTaxonomy::instance( $taxonomyIdentifier, $this->context );
            if ( $taxonomy instanceof OCCalendarSearchTaxonomy )
            {                
                $facets[$taxonomyIdentifier] = array();
                foreach( $taxonomy->getTree() as $item )
                {
                    $facetItem = $this->makeFacetItem( $item, $currentFacetsResult );
                    if ( $facetItem )
                    {
                        $item['is_selectable'] = 1;
                        $facets[$taxonomyIdentifier][] = $facetItem;
                    }                        
                    elseif ( isset( $this->request[ $taxonomyIdentifier ] ) && in_array( $item['id'], $this->request[ $taxonomyIdentifier ] ) )
                    {                            
                        $children = array();
                        if ( $item['children'] > 0 )
                        {
                            foreach( $item['children'] as $child )
                            {
                                $childItem = $this->makeFacetItem( $child, $currentFacetsResult );
                                if ( $childItem )
                                {
                                    $children[] = $childItem;
                                }
                            }
                            $item['children'] = $children;
                        }
                        $item['is_selectable'] = 1;
                        $facets[$taxonomyIdentifier][] = $item;
                    }
                    elseif( in_array( $taxonomyIdentifier, $forceTaxonomyIdentifiers ) )
                    {
                        $item['is_selectable'] = 0;
                        $facets[$taxonomyIdentifier][] = $item;
                    }
                }
            }
        }
        return $facets;
    }
    
    public function makeEvents()
    {
        return $this->solrResult['SearchResult'];
    }
    
    public function makeEventCount()
    {
        return $this->solrResult['SearchCount'];
    }
    
    public function makeDate()
    {
        $date = array();
        if ( $this->start instanceof DateTime )
        {
            $date[] = $this->start->format( 'd/m/Y' );
        }
        if ( $this->end instanceof DateTime )
        {
            $date[] = $this->end->format( 'd/m/Y' );
        }
        return $date;
    }    
    
}
