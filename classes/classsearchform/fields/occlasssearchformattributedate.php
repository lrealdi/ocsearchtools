<?php

class OCClassSearchFormAttributeDate extends OCClassSearchFormAttributeField
{
    protected function __construct( eZContentClassAttribute $attribute )
    {
        parent::__construct( $attribute );
        $this->functionAttributes['bounds'] = 'getBounds';
        $this->functionAttributes['current_bounds'] = 'getCurrentBounds';
    }

    protected function getBounds( $includeCurrentParameters = false )
    {
        $startTimestamp = $endTimestamp = 0;
        $sortField = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, null, 'sort' );
        $currentParameters = array();
        if ( $includeCurrentParameters )
        {
            $currentParameters = OCClassSearchFormHelper::result()->getCurrentParameters();
        }
        $params = array_merge(
            OCClassSearchFormHelper::result()->getBaseParameters(),
            $currentParameters,
            array(
                'SearchContentClassID' => $this->contentClassAttribute->attribute( 'contentclass_id' ),
                'SearchLimit' => 1,
                'SortBy' => array( $sortField => 'asc' )
            )
        );        
        $startSearch = OCFacetNavgationHelper::fetch( $params, OCClassSearchFormHelper::result()->searchText );
        /** @var $startSearchResults eZFindResultNode[] */
        $startSearchResults = $startSearch['SearchResult'];
        if ( isset( $startSearchResults[0] ) )
        {
            $startTimestamp = $startSearchResults[0]->attribute( 'object' )->attribute( 'published' );
        }
        $params['SortBy'] = array( $sortField => 'desc' );
        $endSearch = OCFacetNavgationHelper::fetch( $params, OCClassSearchFormHelper::result()->searchText );
        /** @var $endSearchResults eZFindResultNode[] */
        $endSearchResults = $endSearch['SearchResult'];
        if ( isset( $endSearchResults[0] ) )
        {
            $endTimestamp = $endSearchResults[0]->attribute( 'object' )->attribute( 'published' );
        }

        $data = new OCClassSearchFormDateFieldBounds();
        $data->setStartTimestamp( $startTimestamp );
        $data->setEndTimestamp( $endTimestamp );
        return $data;
    }

    protected function getCurrentBounds()
    {
        if ( $this->attribute( 'value' ) )
        {
            $data = OCClassSearchFormDateFieldBounds::fromString( $this->attribute( 'value' ) );            
        }
        else
        {
            $data = $this->getBounds( true );
        }
        return $data;
    }

    public function buildFetch( OCClassSearchFormFetcher $fetcher, $requestKey, $requestValue, &$filters )
    {
        $fieldName = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, null, 'search' );
        $bounds = OCClassSearchFormDateFieldBounds::fromString( $requestValue );
        $filters[] = $fieldName  . ':[' . $bounds->attribute( 'start_solr' ) . ' TO ' . $bounds->attribute( 'end_solr' ) . ']';
        $fetcher->addFetchField( array(
                'name' => $this->contentClassAttribute->attribute( 'name' ),
                'value' => $bounds->humanString(),
                'remove_view_parameters' => $fetcher->getViewParametersString( array( $requestKey ) )
            ));
    }
}

class OCClassSearchFormDateFieldBounds
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
        $data = new OCClassSearchFormDateFieldBounds();
        $values = explode( self::STRING_SEPARATOR, $string );
        if ( count( $values ) == 2 )
        {
            $data->setStartTimestamp( $values[0] );
            $data->setEndTimestamp( $values[1] );
        }
        else
        {
            $data->setStartTimestamp( $values[0] );
            $data->setEndTimestamp( $values[0] );
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