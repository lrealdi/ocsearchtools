<?php

class OCFacetNavgationHelper
{
    
    const TOKEN = 'questotokenèsegretissimo';
    
    /**
     * @var array
     */
    protected $fetchParams;
    
    /**
     * @var array
     */
    protected $extraParameters = array();
    
    /**
     * @var array
     */
    protected $userFilters;
    
    /**
     * @var array
     */
    public $fetchParameters = array();
    
    /**
     * @var array
     */
    public $originalFetchParameters  = array();
    
    /**
     * @var array
     */
    public $data = array();
    
    /**
     * @var string
     */
    public $baseUri;
    
    /**
     * @var string
     */
    public $queryUri = array();
    
    /**
     * @var string
     */
    public $query = '';
    
    public static $allowedUserParamters = array( 'offset', 'sort_by', 'published' );

    public static $mapper = array(
        'offset' => 'SearchOffset',
        'limit' => 'SearchLimit',
        'facet' => 'Facet',
        'sort_by' => 'SortBy',
        'filter' => 'Filter',
        'class_id' => 'SearchContentClassID',
        'section_id' => 'SearchSectionID',
        'subtree_array' => 'SearchSubTreeArray',
        'as_objects' => 'AsObjects',
        'spell_check' => 'SpellCheck',
        'ignore_visibility' => 'IgnoreVisibility',
        'limitation' => 'Limitation',
        'boost_functions' => 'BoostFunctions',
        'query_handler' => 'QueryHandler',
        'enable_elevation' => 'EnableElevation',
        'force_elevation' => 'ForceElevation',
        'publish_date' => 'SearchDate', 	
        'distributed_search' => 'DistributedSearch',
        'fields_to_return' => 'FieldsToReturn',
        'search_result_clustering' => 'SearchResultClustering',
        'extended_attribute_filter' => 'ExtendedAttributeFilter' 
    );
    
    /**
     * @param array $fetchParams
     * @param array $userFilters
     * @param string $baseUri
     */
    protected function __construct( array $fetchParams, array $userParameters, $baseUri, $query = '' )
    {
        $this->query = $query;
        $this->baseUri = $baseUri;
        $this->originalFetchParameters = $fetchParams;
        $this->fetchParameters = $this->parseFetchParams( $fetchParams );        
        $this->parseUserParams( $userParameters );        
    }
    
    public function getNavigation()
    {        
        $this->data['navigation'] = $this->fetchFacetNavigation();
    }
    
    public function getContents()
    {        
        $result = $this->fetchResults();
        $this->data['contents'] = $result['contents'];
        $this->data['count'] = $result['count'];
        $this->data['uri'] = $this->getUriString( $this->queryUri );        
        $this->data['base_uri'] = $this->baseUri;
        $this->data['json_params'] = json_encode( $this->originalFetchParameters );
        $this->data['token'] = md5( self::TOKEN . json_encode( $this->originalFetchParameters ) );
        $this->data['query'] = $this->query;
        $this->data['fetch_parameters'] = $this->fetchParameters;
    }
    
    public static function data( array $fetchParams, array $userParameters, $baseUri, $query = '' )
    {
        $self = new self( $fetchParams, $userParameters, $baseUri, $query );
        $self->getNavigation();
        $self->getContents();
        return $self->data;
    }
    
    public static function navigation( array $fetchParams, array $userParameters, $baseUri, $query = '' )
    {
        $self = new self( $fetchParams, $userParameters, $baseUri, $query );
        $self->getNavigation();
        return $self->data;
    }
    
    public static function validateToken( $token, $fetchParams )
    {
        return $token == md5( self::TOKEN . json_encode( $fetchParams ) );
    }
    
    public static function encodeValue( $value )
    {
        return urlencode( $value );
    }
    
    public static function decodeValue( $value )
    {
        return urldecode( $value );
    }
    
    protected static function encodeKey( $value )
    {
        return str_replace( ' ', '_', $value );
    }
    
    protected static function decodeKey( $value )
    {
        return str_replace( '_', ' ', $value );
    }
    
    protected function parseUserParams( $userParameters )
    {
        $params = array();
        foreach( $userParameters as $key => $value )
        {
            foreach( $this->fetchParameters['Facet'] as $names )
            {
                if ( self::decodeKey( $key ) == $names['name'] )
                {                                        
                    $params[self::decodeKey( $key )] = self::decodeValue( $value );
                    $this->queryUri[self::encodeKey( $key )] = self::encodeValue( $value );
                    $filterValue = addcslashes( $value, '"' );
                    $this->fetchParameters['Filter'][] = "{$names['field']}:\"{$filterValue}\"";
                }
            }
            
            foreach( self::$allowedUserParamters as $param )
            {                
                if ( isset( $userParameters[$param] ) && isset( self::$mapper[$param] ) )
                {
                    $this->fetchParameters[self::$mapper[$param]] = $userParameters[$param];
                }
            }
            
            foreach( $this->extraParameters as $filter => $name )
            {
                if ( self::decodeKey( $key ) == $name )
                {
                    $this->fetchParameters[$filter] = $value;
                }
            }
            if ( $key == 'query' && $this->query == '' )
            {
                $this->query = $value;               
            }
        }         
    }
    
    protected function parseFetchParams( $fetchParams )
    {
        $params = array();
        foreach( $fetchParams as $key => $value )
        {
            if ( isset( self::$mapper[$key] ) )
            {
                $params[self::$mapper[$key]] = $value;
            }
        }
        if ( isset( $fetchParams['extra'] ) )
        {
            $this->extraParameters = $fetchParams['extra'];
        }
        return $params;
    }
    
    protected static function addToQueryUri( &$queryUri, $key, $value, $baseUrl )
    {
        $queryUri[$key] = $value;
        return self::getUriString( $queryUri, $baseUrl );
    }
    
    protected static function removeFromQueryUri( &$queryUri, $key, $value, $baseUrl )
    {
        if ( isset( $queryUri[$key] ) && $queryUri[$key] == $value )
        {
            unset( $queryUri[$key] );
        }
        return self::getUriString( $queryUri, $baseUrl );
    }
    
    protected static function getUriString( $queryUri, &$baseUrl )
    {                
        foreach( $queryUri as $key => $value )
        {            
            if ( !empty( $value ) )
            {
                $baseUrl .= "/($key)/$value";
            }
        }
        return $baseUrl;
    }
    
    protected static function mergeFacetsInNavigationList( $facets, $facetFields1, $facetFields2 )
    {
        $navigation = array();
        foreach( $facets as $key => $names )
        {            
            $navigation[$names['name']] = array();
            foreach( $facetFields1[$key]['queryLimit'] as $term => $query )
            {
                if ( empty( $term ) ) continue;
                
                $navigationValues = array();
                $navigationValues['name'] = $term;
                if ( strpos( $query, 'yearmonth____dt' ) !== false)
                {
                    $navigationValues['name'] = DateTime::createFromFormat( "Y-m-d\TH:i:sP", $term )->format ("F Y");
                }
                if ( strpos( $query, 'year____dt' ) !== false )
                {
                    $navigationValues['name'] = DateTime::createFromFormat( "Y-m-d\TH:i:sP", $term )->format ("Y");
                }                
                //$navigationValues['filter'] = $query;                
                $nameEncoded = self::encodeKey( $names['name'] );
                $termEncoded = self::encodeValue( $term );
                if ( isset( $queryUrl[$nameEncoded] ) && $queryUrl[$nameEncoded] == $termEncoded )
                {
                    $navigationValues['active'] = true;
                    $navigationValues['url'] = self::removeFromQueryUri( $queryUrl, $nameEncoded, $termEncoded, $baseUrl );
                }
                else
                {
                    $navigationValues['active'] = false;
                    $navigationValues['url'] = self::addToQueryUri( $queryUrl, $nameEncoded, $termEncoded, $baseUrl );
                }
                
                $navigation[$names['name']][$term] = $navigationValues;
            }
            
            foreach( $facetFields1[$key]['nameList'] as $term => $name )
            {                
                if ( empty( $term ) ) continue;
                $navigation[$names['name']][$term]['query'] = trim( $name, '"' );
            }

            if ( isset( $facetFields2[$key]['countList'] ) && !empty( $facetFields2[$key]['countList'] ) )
            {
                foreach( $facetFields1[$key]['countList'] as $term => $count )
                {                
                    if ( empty( $term ) ) continue;
                    $navigation[$names['name']][$term]['count'] = 0;
                }
                foreach( $facetFields2[$key]['countList'] as $term => $count )
                {                
                    if ( empty( $term ) ) continue;
                    $navigation[$names['name']][$term]['count'] = $count;                                        
                }                
            }
            else
            {
                foreach( $facetFields1[$key]['countList'] as $term => $count )
                {                
                    if ( empty( $term ) ) continue;
                    $navigation[$names['name']][$term]['count'] = $count;
                }
            }
        }
        return $navigation; 
    }
    
    public static function navigationList( $absoluteParams, $relativeParams, $query = '', $extraParameters = array(), &$queryUrl = '', &$baseUrl = ''  )
    {
        $navigation = array();
        
        $absoluteParams['SearchLimit'] = 1;
        $absoluteParams['AsObjects'] = false;                
        $search = self::fetch( $absoluteParams );
        
        $searchForCount = array();
        if ( !empty( $relativeParams ) )
        {
            $relativeParams['SearchLimit'] = 1;
            $relativeParams['AsObjects'] = false;
            $searchForCount = self::fetch( $relativeParams, $query );
        }        
        
        if ( isset( $extraParameters['SearchDate'] ) )
        {
            $activeSearchDate = -1;
            if ( isset( $relativeParams['SearchDate'] ) )
            {
                $activeSearchDate = $relativeParams['SearchDate'];
            }
            $nameEncoded = self::encodeKey( $extraParameters['SearchDate'] );
            $navigation[$extraParameters['SearchDate']] = array(
                //ezpI18n::tr( "design/standard/content/search", "Any time" ) => array(
                //    'name' => ezpI18n::tr( "design/standard/content/search", "Any time" ),
                //    'url' =>  $activeSearchDate == "-1" ? self::removeFromQueryUri( $queryUrl, $nameEncoded, $termEncoded, $baseUrl ) : self::addToQueryUri( $queryUrl, $nameEncoded, -1, $baseUrl ),
                //    'active' =>  $activeSearchDate == "-1" ? true : false
                //),
                ezpI18n::tr( "design/standard/content/search", "Last day" ) => array(
                    'name' => ezpI18n::tr( "design/standard/content/search", "Last day" ),
                    'url' =>  $activeSearchDate == "1" ? self::removeFromQueryUri( $queryUrl, $nameEncoded, $termEncoded, $baseUrl ) : self::addToQueryUri( $queryUrl, $nameEncoded, 1, $baseUrl ),
                    'active' =>  $activeSearchDate == "1" ? true : false,
                    'count' => false,
                    'query' => 1
                ),
                ezpI18n::tr( "design/standard/content/search", "Last week" ) => array(
                    'name' => ezpI18n::tr( "design/standard/content/search", "Last week" ),
                    'url' =>  $activeSearchDate == "2" ? self::removeFromQueryUri( $queryUrl, $nameEncoded, $termEncoded, $baseUrl ) : self::addToQueryUri( $queryUrl, $nameEncoded, 2, $baseUrl ),
                    'active' =>  $activeSearchDate == "2" ? true : false,
                    'count' => false,
                    'query' => 2
                ),
                ezpI18n::tr( "design/standard/content/search", "Last month" ) => array(
                    'name' => ezpI18n::tr( "design/standard/content/search", "Last month" ),
                    'url' =>  $activeSearchDate == "3" ? self::removeFromQueryUri( $queryUrl, $nameEncoded, $termEncoded, $baseUrl ) : self::addToQueryUri( $queryUrl, $nameEncoded, 3, $baseUrl ),
                    'active' =>  $activeSearchDate == "3" ? true : false,
                    'count' => false,
                    'query' => 3
                ),
            );
        }
        
        $facetFields = $search['SearchExtras']->attribute( 'facet_fields' );
        $facetFieldsForCount = isset( $searchForCount['SearchExtras'] ) ? $searchForCount['SearchExtras']->attribute( 'facet_fields' ) : array();

        return self::mergeFacetsInNavigationList( $absoluteParams['Facet'], $facetFields, $facetFieldsForCount );
    }
    
    public static function map( $parameters )
    {
        $params = array();
        foreach( $parameters as $key => $value )
        {
            if ( isset( self::$mapper[$key] ) )
            {
                $params[self::$mapper[$key]] = $value;
            }
        }
        return $params;
    }
    
    protected function fetchFacetNavigation()
    {        
        $params = $this->parseFetchParams( $this->originalFetchParameters );
        $paramsForCount = $this->fetchParameters;           
        return self::navigationList( $params, $paramsForCount, $this->query, $this->extraParameters, $this->queryUri, $this->baseUri );
    }
    
    protected function fetchResults()
    {
        $search = self::fetch( $this->fetchParameters, $this->query );
        return array(
            'contents' => $search['SearchResult'],
            'count' => $search['SearchCount']
        );
    }
    
    public static function fetch( $parameters, $query = '' )
    {
        $solrFetchParams = array(
            'SearchOffset' => 0,
            'SearchLimit' => 0,
            'Facet' => null,
            'SortBy' => null,
            'Filter' => null,
            'SearchContentClassID' => null,
            'SearchSectionID' => null,
            'SearchSubTreeArray' => null,
            'AsObjects' => true,
            'SpellCheck' => null,
            'IgnoreVisibility' => null,
            'Limitation' => null,
            'BoostFunctions' => null,
            'QueryHandler' => 'ezpublish',
            'EnableElevation' => true,
            'ForceElevation' => true,
            'SearchDate' => null,
            'DistributedSearch' => null,
            'FieldsToReturn' => null,
            'SearchResultClustering' => null,
            'ExtendedAttributeFilter' => array()
        );
        $fetchParameters = array_merge( $solrFetchParams, $parameters );
        $solrSearch = new eZSolr();
        return $solrSearch->search( $query, $fetchParameters );
    }
}

?>