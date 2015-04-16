<?php

abstract class OCClassSearchFormAttributeBoundable extends OCClassSearchFormAttributeField
{
    /**
     * @return OCClassSearchFormFieldBoundsInterface
     */
    abstract function getBoundsClassName();

    protected function __construct( eZContentClassAttribute $attribute )
    {
        parent::__construct( $attribute );
        $this->functionAttributes['bounds'] = 'getBounds';
        $this->functionAttributes['current_bounds'] = 'getCurrentBounds';
    }

    public function buildFetch( OCClassSearchFormFetcher $fetcher, $requestKey, $requestValue, &$filters )
    {
        $className = $this->getBoundsClassName();
        $fieldName = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, null, 'search' );
        /** @var OCClassSearchFormFieldBoundsInterface $bounds */
        $bounds = $className::fromString( $requestValue );
        $filters[] = $fieldName  . ':[' . $bounds->attribute( 'start_solr' ) . ' TO ' . $bounds->attribute( 'end_solr' ) . ']';
        $fetcher->addFetchField( array(
            'name' => $this->contentClassAttribute->attribute( 'name' ),
            'value' => $bounds->humanString(),
            'remove_view_parameters' => $fetcher->getViewParametersString( array( $requestKey ) )
        ));
    }

    protected function getBounds( $includeCurrentParameters = false )
    {
        $start = $end = false;
        $sortField = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, null, 'sort' );
        $currentParameters = array();
        if ( $includeCurrentParameters )
        {
            $currentParameters = OCClassSearchFormHelper::result()->getCurrentParameters();
        }
        $fieldName = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, null, 'search' );
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
            $dataMap = $startSearchResults[0]->attribute( 'object' )->attribute( 'data_map' );                        
            $start = $dataMap[$this->contentClassAttribute->attribute( 'identifier' )];
        }        
        $params['SortBy'] = array( $sortField => 'desc' );
        $endSearch = OCFacetNavgationHelper::fetch( $params, OCClassSearchFormHelper::result()->searchText );
        /** @var $endSearchResults eZFindResultNode[] */
        $endSearchResults = $endSearch['SearchResult'];
        if ( isset( $endSearchResults[0] ) )
        {
            $dataMap = $endSearchResults[0]->attribute( 'object' )->attribute( 'data_map' );
            $end = $dataMap[$this->contentClassAttribute->attribute( 'identifier' )];
        }

        $className = $this->getBoundsClassName();
        /** @var OCClassSearchFormFieldBoundsInterface $bounds */
        $bounds = new $className();
        $bounds->setStart( $start );
        $bounds->setEnd( $end );
        return $bounds;
    }

    protected function getCurrentBounds()
    {
        if ( $this->attribute( 'value' ) )
        {
            $className = $this->getBoundsClassName();
            $data = $className::fromString( $this->attribute( 'value' ) );
        }
        else
        {
            $data = $this->getBounds( true );
        }
        return $data;
    }
}