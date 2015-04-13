<?php
class OCClassSearchFormAttributeRelations extends OCClassSearchFormAttributeField
{
    protected function getValues()
    {        
        if ( $this->values === null )
        {
            $this->values = array();
            //@todo filter per parent_node
            //$classContent = $this->contentClassAttribute->content();
            //$filters = isset( $classContent['default_placement']['node_id'] ) ?  array( $classContent['default_placement']['node_id'] ) : array( 1 );
            
            //@todo errore nella definzione del nome del sottoattributo? verifaicare vedi anche in self::buildFetch
            //$field = ezfSolrDocumentFieldBase::$DocumentFieldName->lookupSchemaName(
            //    ezfSolrDocumentFieldBase::SUBMETA_FIELD_PREFIX . $this->contentClassAttribute->attribute( 'identifier' ) . ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR . 'name',
            //    'string');
            
            $field = ezfSolrDocumentFieldBase::$DocumentFieldName->lookupSchemaName(
                ezfSolrDocumentFieldBase::SUBATTR_FIELD_PREFIX . $this->contentClassAttribute->attribute( 'identifier' ) . ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR . 'name' . ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR,
                'string' );
                
            $facets = array( 'field' => $field, 'name'=> $this->attributes['name'], 'limit' => 300, 'sort' => 'alpha' );
            
            $fetchParameters = OCClassSearchFormHelper::result()->buildFetch();
            $currentParameters = array();
            $scopeParameters = array( 'SearchContentClassID' => array( $this->contentClassAttribute->attribute( 'contentclass_id' ) ),
                                      'Facet' => array( $facets ) );
            if ( isset( $fetchParameters['class_id'] ) && $fetchParameters['class_id'] == $this->contentClassAttribute->attribute( 'contentclass_id' ) )
            {
                $currentParameters = array_merge( OCFacetNavgationHelper::map( $fetchParameters ), $scopeParameters );
            }
            
            $params = array_merge( $currentParameters, $scopeParameters );
            $data = OCFacetNavgationHelper::navigationList( $scopeParameters, $currentParameters, OCClassSearchFormHelper::result()->searchText, OCClassSearchFormHelper::result()->isFetch() );

            if ( isset( $data[$this->attributes['name']] ) )
            {
                $this->values = $data[$this->attributes['name']];
                // setto i valori attivi e inietto il conto nel nome
                foreach( $this->values as $index => $value )
                {
                    $current = (array) $this->attributes['value'];
                    if ( in_array( $value['query'], $current ) )
                    {
                        $this->values[$index]['active'] = true;
                    }
                    
                    $this->values[$index]['query'] = OCFacetNavgationHelper::encodeValue( $this->values[$index]['query'] );
                    $this->values[$index]['raw_name'] = $value['name'];
                    
                    if ( isset( $value['count'] ) && $value['count'] > 0 )
                    {
                        $this->values[$index]['name'] = $value['name'] . ' (' . $value['count'] . ')';
                        $this->values[$index]['count'] = $value['count'];
                    }
                }
            }            
        }        
        return $this->values;
    }
    
    public function buildFetch( OCClassSearchFormFetcher $fetcher, $requestKey, $requestValue, &$filters )
    {
        if ( is_array( $requestValue ) && count( $requestValue ) == 1 )
        {
            $requestValue = array_shift( $requestValue );
        }
        
        //@todo errore nella definzione del nome del sottoattributo? verifaicare vedi anceh in self::getValues
        //$fieldName = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, 'name', 'search' );
        $fieldName = ezfSolrDocumentFieldBase::$DocumentFieldName->lookupSchemaName(
                ezfSolrDocumentFieldBase::SUBATTR_FIELD_PREFIX . $this->contentClassAttribute->attribute( 'identifier' ) . ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR . 'name' . ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR,
                'string' );            
        if ( is_array( $requestValue ) )
        {
            $values = array( 'or' );
            foreach( $requestValue as $v )
            {
                $values[] = $fieldName . ':' . $fetcher->encode( $v, true );
            }
            $filters[] = $values;
        }
        else
        {            
            $filters[] = $fieldName . ':' . $fetcher->encode( $requestValue, true );
        }
        
        $fetcher->addFetchField( array(
            'name' => $this->contentClassAttribute->attribute( 'name' ),
            'value' => $requestValue,
            'remove_view_parameters' => $fetcher->getViewParametersString( array( $requestKey ) )
        ));
    }
}