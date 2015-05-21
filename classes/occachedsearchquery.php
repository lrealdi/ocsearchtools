<?php

class OCCachedSearchQuery extends OCSearchQuery
{
    const CACHE_IDENTIFIER = 'calendarquery';

    protected $cacheKey = 'default';

    public $queryText;

    public $queryParameters = array();
    
    protected $baseQueryParameters = array(
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

    public function __construct( $cacheKey )
    {
        $this->cacheKey = $cacheKey;
    }

    public function fetch()
    {
        $parameters = array(
            'queryText' => $this->queryText,
            'queryParameters' => array_merge( $this->baseQueryParameters, $this->queryParameters )
        );
        $currentSiteAccess = $GLOBALS['eZCurrentAccess']['name'];
        $copyParameters = $parameters;
        array_multisort( $copyParameters );
        $cacheFileName = $this->cacheKey . '_' . md5( json_encode( $copyParameters ) ) . '.cache';
        $cacheFilePath = eZDir::path( array( eZSys::cacheDirectory(), static::cacheDirectory(), $currentSiteAccess, $cacheFileName ) );
        $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );

        return $cacheFile->processCache(
            array( 'OCCachedSearchQuery', 'retrieveCache' ),
            array( 'OCCachedSearchQuery', 'generateCache' ),
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
        $result = false;
        if ( isset( $parameters ) )
        {
            $solrSearch = new OCSolr();
            $result = $solrSearch->search(
                $parameters['queryText'],
                $parameters['queryParameters']
            );
            $extras = $result['SearchExtras'];
            if ( $extras instanceof ezfSearchResultInfo )
            {
                $result['FacetFields'] =  $extras->attribute( 'facet_fields' );
            }
            unset( $result['SearchExtras'] );
        }
        return array(
            'content' => $result,
            'scope' => self::CACHE_IDENTIFIER
        );
    }

    public static function clearCache()
    {
        eZDebug::writeNotice( "Clear calendar query cache", __METHOD__ );
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

        $cacheBaseDir = eZDir::path( array( eZSys::cacheDirectory(), static::cacheDirectory() ) );
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

    public function addQueryFilter( $data )
    {
        if ( $data )
        {
            $this->queryParameters['Filter'][] = $data;
        }
    }

    public function addQueryFacet( $data )
    {
        if ( $data )
        {
            $this->queryParameters['Facet'][] = $data;
        }
    }

}