<?php

class OCCalendarSearchTaxonomy
{
    const CACHE_IDENTIFIER = 'calendartaxonomy';
    
    protected $taxonomyIdentifier;
    
    protected $context;
    
    public static function instance( $taxonomyIdentifier, OCCalendarSearchContext $context )
    {
        return new OCCalendarSearchTaxonomy( $taxonomyIdentifier, $context );
    }
    
    protected function __construct( $taxonomyIdentifier, OCCalendarSearchContext $context )
    {
        $this->taxonomyIdentifier = $taxonomyIdentifier;
        $this->context = $context;
        
        $currentSiteAccess = $GLOBALS['eZCurrentAccess']['name'];
        $cacheFileName = $this->taxonomyIdentifier . '_' . $this->context->cacheKey() . '.cache';
        $cacheFilePath = eZDir::path( array( eZSys::cacheDirectory(), self::cacheDirectory(), $currentSiteAccess, $cacheFileName ) );
        
        $parameters = array(
            'taxonomyIdentifier' => $this->taxonomyIdentifier,
            'context' => $this->context
        );

        $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
        $this->data = $cacheFile->processCache(
            array( 'OCCalendarSearchTaxonomy', 'retrieveCache' ),
            array( 'OCCalendarSearchTaxonomy', 'generateCache' ),
            null,
            null,
            compact( 'parameters' )
        );
    }
    
    public function getTree()
    {
        return $this->data;
    }
    
    public function getItem( $id )
    {
        foreach( $this->data as $item )
        {
            if ( $item['id'] == $id )
            {
                return $item;
            }
            elseif ( count( $item['children'] ) > 0 )
            {
                foreach( $item['children'] as $child )
                {
                    if ( $child['id'] == $id )
                    {
                        return $child;
                    }
                }
            }
        }
        return false;
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
    
    public static function retrieveCache( $file, $mtime, $args )
    {
        $result = include( $file );
        return $result;
    }
    
    public static function generateCache( $file, $args )
    {
        extract( $args );
        eZDebug::writeNotice( "Generate calendartaxonomy " . $parameters['taxonomyIdentifier'] . " for " . $parameters['context']->cacheKey(), __METHOD__ );
        $result = $parameters['context']->taxonomyTree( $parameters['taxonomyIdentifier'] );
        return array( 'content' => $result,
                      'scope'   => self::CACHE_IDENTIFIER );
    }
    
    public static function clearCache()
    {        
        eZDebug::writeNotice( "Clear calendar taxonomy cache", __METHOD__ );
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
    }
    
    public function getSolrFilters( array $data )
    {
        return $this->context->getSolrFilters( $data, $this );        
    }    
}
