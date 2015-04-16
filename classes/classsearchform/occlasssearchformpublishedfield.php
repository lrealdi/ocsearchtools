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
    
    protected function getBounds( $includeCurrentParameters = false )
    {        
        $startTimestamp = $endTimestamp = 0;
        $currentParameters = array();
        if ( $includeCurrentParameters )
        {
            $currentParameters = OCClassSearchFormHelper::result()->getCurrentParameters();
        }
        $params = array_merge(
            OCClassSearchFormHelper::result()->getBaseParameters(),
            $currentParameters,
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
        $data->setStart( $startTimestamp );
        $data->setEnd( $endTimestamp );
        return $data;
    }
    
    protected function getCurrentBounds()
    {
        $currentParameters = OCClassSearchFormHelper::result()->getCurrentParameters();
        if ( $this->attribute( 'value' ) && $currentParameters['class_id'] == $this->currentClassId )
        {
            $data = OCClassSearchFormPublishedFieldBounds::fromString( $this->attribute( 'value' ) );            
        }
        else
        {
            $data = $this->getBounds( $currentParameters['class_id'] == $this->currentClassId );
        }
        return $data;
    }    
}


class OCClassSearchFormPublishedFieldBounds extends OCClassSearchFormDateFieldBounds
{

}