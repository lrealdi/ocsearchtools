<?php

class OCClassSearchFormPublishedField extends OCClassSearchFormField
{
    public $currentClassId;
    
    public function __construct( $currentClassId = null )
    {        
        $this->currentClassId = $currentClassId;
        $this->attributes = array(            
            'label' => ezpI18n::tr( 'extension/ocsearchtools', 'Periodo di pubblicazione' ),
            'name' => 'publish_date',
            'id' => 'publish_date',
            'value' => OCClassSearchFormHelper::result()->requestField( 'publish_date' )
        );
        $this->functionAttributes = array(
            'bounds' => 'getBounds',
            'current_bounds' => 'getCurrentBounds'
        );
    }
    
    public function buildFetch( OCClassSearchFormFetcher $fetcher, $requestValue, &$filters )
    {        
        $bounds = OCClassSearchFormPublishedFieldBounds::fromString( $this->attributes['value'] );
        $filters[] = eZSolr::getMetaFieldName( 'published' ) . ':[' . $bounds->attribute( 'start_solr' ) . ' TO ' . $bounds->attribute( 'end_solr' ) . ']';
        $fetcher->addFetchField( array(
            'name' => $this->attributes['label'],
            'value' => $bounds->humanString(),
            'remove_view_parameters' => $fetcher->getViewParametersString( array( 'publish_date' ) )
        ));        
    }
    
    protected function getBounds()
    {        
        $startTimestamp = $endTimestamp = 0;
        
        $params = array_merge(
            OCFacetNavgationHelper::map( OCClassSearchFormHelper::result()->getBaseParameters() ),
            array(
                'SearchContentClassID' => $this->currentClassId !== null ? array( $this->currentClassId ) : null,
                'SearchLimit' => 1,
                'SortBy' => array( 'published' => 'asc' )
            )
        );    
        $startSearch = OCFacetNavgationHelper::fetch( $params, OCClassSearchFormHelper::result()->searchText );        
        if ( isset( $startSearch['SearchResult'][0] ) )
        {
            $startTimestamp = $startSearch['SearchResult'][0]->attribute( 'object' )->attribute( 'published' );
        }        
        $params['SortBy'] = array( 'published' => 'desc' );
        $endSearch = OCFacetNavgationHelper::fetch( $params, OCClassSearchFormHelper::result()->searchText );        
        if ( isset( $endSearch['SearchResult'][0] ) )
        {
            $endTimestamp = $endSearch['SearchResult'][0]->attribute( 'object' )->attribute( 'published' );
        }
        
        $data = new OCClassSearchFormPublishedFieldBounds();
        $data->setStartTimestamp( $startTimestamp );
        $data->setEndTimestamp( $endTimestamp );
        return $data;
    }
    
    protected function getCurrentBounds()
    {
        if ( $this->attribute( 'value' ) )
        {
            $data = OCClassSearchFormPublishedFieldBounds::fromString( $this->attribute( 'value' ) );            
        }
        else
        {
            $data = $this->getBounds();
        }
        return $data;
    }    
}


class OCClassSearchFormPublishedFieldBounds
{
    const STRING_SEPARATOR = '-';
    
    protected $start;
    protected $end;
    
    public function __construct()
    {
        $this->start = new DateTime();
        $this->end = new DateTime();
    }
    
    public function attributes()
    {
        return array(
            'start_timestamp',
            'start_js',
            'start_solr',
            'end_timestamp',
            'end_js',
            'end_solr'
        );
    }
    
    public function attribute( $key )
    {
        switch( $key )
        {
            case 'start_timestamp':
                return $this->start->format( 'U' );
                break;            
            case 'start_js':
                return $this->start->format( 'U' ) * 1000;
                break;
            case 'start_solr':
                return ezfSolrDocumentFieldBase::preProcessValue( $this->start->format( 'U' ), 'date' );
                break;            
            case 'end_timestamp':
                return $this->end->format( 'U' );
                break;            
            case 'end_js':
                return $this->end->format( 'U' ) * 1000;
                break;
            case 'end_solr':
                return ezfSolrDocumentFieldBase::preProcessValue( $this->end->format( 'U' ), 'date' );
                break;            
            default: return false;
        }
    }
    
    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }
    
    public static function fromString( $string )
    {
        $data = new OCClassSearchFormPublishedFieldBounds();
        $values = explode( self::STRING_SEPARATOR, $string );
        if ( count( $values ) == 2 )
        {
            $data->setStartTimestamp( $values[0] );            
            $data->setEndTimestamp( $values[1] );
        }
        return $data;
    }
    
    public function setStartTimestamp( $timestamp )
    {        
        $this->start->setTimestamp( $timestamp );
        $this->start->setTime( 00, 00 );
    }
    
    public function setEndTimestamp( $timestamp )
    {
        $this->end->setTimestamp( $timestamp );
        $this->end->setTime( 23, 59 );
    }
    
    public function humanString()
    {
        return $this->start->format( OCCalendarData::PICKER_DATE_FORMAT ) . ' → ' . $this->end->format( OCCalendarData::PICKER_DATE_FORMAT );
    }
    
    public function __toString()
    {
        return $this->start->format( 'U' ) . self::STRING_SEPARATOR . $this->end->format( 'U' );
    }
}