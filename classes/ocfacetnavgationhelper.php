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
    
    public $allowedUserParamters = array( 'offset' );

    private $mapper = array(
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
        $this->data['navigation'] = $this->fetchFacetNavigation();
        $result = $this->fetchResults();
        $this->data['contents'] = $result['contents'];
        $this->data['count'] = $result['count'];
        $this->data['uri'] = $this->getUriString( $this->queryUri );        
        $this->data['base_uri'] = $this->baseUri;
        $this->data['json_params'] = json_encode( $this->originalFetchParameters );
        $this->data['token'] = md5( self::TOKEN . json_encode( $this->originalFetchParameters ) );
        $this->data['query'] = $this->query;
        $this->data['fetch_paramters'] = $this->fetchParameters;
    }
    
    public static function data( array $fetchParams, array $userParameters, $baseUri, $query = '' )
    {
        $self = new self( $fetchParams, $userParameters, $baseUri, $query );
        return $self->data;
    }
    
    public static function validateToken( $token, $fetchParams )
    {
        return $token == md5( self::TOKEN . json_encode( $fetchParams ) );
    }
    
    protected function encodeValue( $value )
    {
        return urlencode( $value );
    }
    
    protected function decodeValue( $value )
    {
        return urldecode( $value );
    }
    
    protected function encodeKey( $value )
    {
        return str_replace( ' ', '_', $value );
    }
    
    protected function decodeKey( $value )
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
                if ( $this->decodeKey( $key ) == $names['name'] )
                {                                        
                    $params[$this->decodeKey( $key )] = $this->decodeValue( $value );
                    $this->queryUri[$this->encodeKey( $key )] = $this->encodeValue( $value );
                    $filterValue = addcslashes( $value, '"' );
                    $this->fetchParameters['Filter'][] = "{$names['field']}:\"{$filterValue}\"";
                }
            }
            
            foreach( $this->allowedUserParamters as $param )
            {                
                if ( isset( $userParameters[$param] ) && isset( $this->mapper[$param] ) )
                {
                    $this->fetchParameters[$this->mapper[$param]] = $userParameters[$param];
                }
            }
            
            foreach( $this->extraParameters as $filter => $name )
            {
                if ( $this->decodeKey( $key ) == $name )
                {
                    $this->fetchParameters[$filter] = $value;
                }
            }
        }         
    }
    
    protected function parseFetchParams( $fetchParams )
    {
        $params = array();
        foreach( $fetchParams as $key => $value )
        {
            if ( isset( $this->mapper[$key] ) )
            {
                $params[$this->mapper[$key]] = $value;
            }
        }
        if ( isset( $fetchParams['extra'] ) )
        {
            $this->extraParameters = $fetchParams['extra'];
        }
        return $params;
    }
    
    protected function addToQueryUri( $queryUri, $key, $value )
    {
        $queryUri[$key] = $value;
        return $this->getUriString( $queryUri );
    }
    
    protected function removeFromQueryUri( $queryUri, $key, $value )
    {
        if ( isset( $queryUri[$key] ) && $queryUri[$key] == $value )
        {
            unset( $queryUri[$key] );
        }
        return $this->getUriString( $queryUri );
    }
    
    protected function getUriString( $queryUri )
    {        
        $baseUri = $this->baseUri;
        foreach( $queryUri as $key => $value )
        {            
            if ( !empty( $value ) )
            {
                $baseUri .= "/($key)/$value";
            }
        }
        return $baseUri;
    }
    
    protected function fetchFacetNavigation()
    {        
        $navigation = array();
        
        $params = $this->parseFetchParams( $this->originalFetchParameters );
        $params['SearchLimit'] = 1;
        $params['AsObjects'] = false;
        
        $search = self::fetch( $params );
        
        $paramsForCount = $this->fetchParameters;
        $paramsForCount['SearchLimit'] = 1;
        $paramsForCount['AsObjects'] = false;
        $searchForCount = self::fetch( $paramsForCount, $this->query );
        
        if ( isset( $this->extraParameters['SearchDate'] ) )
        {
            $activeSearchDate = -1;
            if ( isset( $this->fetchParameters['SearchDate'] ) )
            {
                $activeSearchDate = $this->fetchParameters['SearchDate'];
            }
            $nameEncoded = $this->encodeKey( $this->extraParameters['SearchDate'] );
            $navigation[$this->extraParameters['SearchDate']] = array(
                //ezpI18n::tr( "design/standard/content/search", "Any time" ) => array(
                //    'name' => ezpI18n::tr( "design/standard/content/search", "Any time" ),
                //    'url' =>  $activeSearchDate == "-1" ? $this->removeFromQueryUri( $this->queryUri, $nameEncoded, $termEncoded ) : $this->addToQueryUri( $this->queryUri, $nameEncoded, -1 ),
                //    'active' =>  $activeSearchDate == "-1" ? true : false
                //),
                ezpI18n::tr( "design/standard/content/search", "Last day" ) => array(
                    'name' => ezpI18n::tr( "design/standard/content/search", "Last day" ),
                    'url' =>  $activeSearchDate == "1" ? $this->removeFromQueryUri( $this->queryUri, $nameEncoded, $termEncoded ) : $this->addToQueryUri( $this->queryUri, $nameEncoded, 1 ),
                    'active' =>  $activeSearchDate == "1" ? true : false,
                    'count' => false
                ),
                ezpI18n::tr( "design/standard/content/search", "Last week" ) => array(
                    'name' => ezpI18n::tr( "design/standard/content/search", "Last week" ),
                    'url' =>  $activeSearchDate == "2" ? $this->removeFromQueryUri( $this->queryUri, $nameEncoded, $termEncoded ) : $this->addToQueryUri( $this->queryUri, $nameEncoded, 2 ),
                    'active' =>  $activeSearchDate == "2" ? true : false,
                    'count' => false
                ),
                ezpI18n::tr( "design/standard/content/search", "Last month" ) => array(
                    'name' => ezpI18n::tr( "design/standard/content/search", "Last month" ),
                    'url' =>  $activeSearchDate == "3" ? $this->removeFromQueryUri( $this->queryUri, $nameEncoded, $termEncoded ) : $this->addToQueryUri( $this->queryUri, $nameEncoded, 3 ),
                    'active' =>  $activeSearchDate == "3" ? true : false,
                    'count' => false
                ),
            );
        }
        
        $facetFields = $search['SearchExtras']->attribute( 'facet_fields' );
        $facetFieldsForCount = $searchForCount['SearchExtras']->attribute( 'facet_fields' );
        foreach( $this->fetchParameters['Facet'] as $key => $names )
        {
            $navigation[$names['name']] = array();
            foreach( $facetFields[$key]['queryLimit'] as $term => $query )
            {
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
                $nameEncoded = $this->encodeKey( $names['name'] );
                $termEncoded = $this->encodeValue( $term );
                if ( isset( $this->queryUri[$nameEncoded] ) && $this->queryUri[$nameEncoded] == $termEncoded )
                {
                    $navigationValues['active'] = true;
                    $navigationValues['url'] = $this->removeFromQueryUri( $this->queryUri, $nameEncoded, $termEncoded );
                }
                else
                {
                    $navigationValues['active'] = false;
                    $navigationValues['url'] = $this->addToQueryUri( $this->queryUri, $nameEncoded, $termEncoded );
                }
                
                $navigation[$names['name']][$term] = $navigationValues;
            }

            if ( isset( $facetFieldsForCount[$key] ) )
            {
                foreach( $facetFieldsForCount[$key]['countList'] as $term => $count )
                {                
                    $navigation[$names['name']][$term]['count'] = $count;
                }
            }
            
            foreach( $facetFields[$key]['countList'] as $term => $count )
            {                
                if ( !isset( $navigation[$names['name']][$term]['count'] ) )
                {
                    $navigation[$names['name']][$term]['count'] = 0;
                }
            }
            foreach( $facetFields[$key]['nameList'] as $term => $name )
            {                
                $navigation[$names['name']][$term]['query'] = $name;
            }
        }
        return $navigation;
    }
    
    protected function fetchResults()
    {
        $search = self::fetch( $this->fetchParameters, $this->query );
        return array(
            'contents' => $search['SearchResult'],
            'count' => $search['SearchCount']
        );
    }
    
    protected static function fetch( $parameters, $query = '' )
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