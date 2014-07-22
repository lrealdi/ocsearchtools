<?php

class OCClassSearchFormAttributeField extends OCClassSearchFormField
{
    const NAME_PREFIX = 'attribute';
    
    protected static $_instances = array();
    
    protected $values;
    
    public $contentClassAttribute;
    
    protected function __construct( eZContentClassAttribute $attribute )
    {        
        $this->contentClassAttribute = $attribute;
        $this->attributes = array(
           'id' => $attribute->attribute( 'id' ), 
           'name' => self::NAME_PREFIX . $attribute->attribute( 'id' ), 
           'value' => OCClassSearchFormHelper::result()->requestField( self::NAME_PREFIX . $attribute->attribute( 'id' ) ), 
           'class_attribute' => $attribute           
        );        
        $this->functionAttributes = array( 'values' => 'getValues' );
    }

    /**
     * @param eZContentClassAttribute $attribute
     *
     * @return OCClassSearchFormAttributeField
     */
    public static function instance( eZContentClassAttribute $attribute )
    {
        if ( !isset( self::$_instances[$attribute->attribute( 'id' )] ) )
        {
            self::$_instances[$attribute->attribute( 'id' )] = new OCClassSearchFormAttributeField( $attribute );
        }
        return self::$_instances[$attribute->attribute( 'id' )];
    }
    
    protected function getValues()
    {        
        if ( $this->values === null )
        {
            $this->values = array();
            if ( $this->contentClassAttribute->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
            {
                //@todo filter per parent_node
                //$classContent = $this->contentClassAttribute->content();
                //$filters = isset( $classContent['default_placement']['node_id'] ) ?  array( $classContent['default_placement']['node_id'] ) : array( 1 );
                $field = ezfSolrDocumentFieldBase::generateSubattributeFieldName( $this->contentClassAttribute, 'name', 'string' );
            }
            else
            {            
                $field = ezfSolrDocumentFieldBase::generateAttributeFieldName( $this->contentClassAttribute, 'string' );
            }
            
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
                    
                    if ( isset( $value['count'] ) && $value['count'] > 0 )
                    {
                        $this->values[$index]['name'] = $value['name'] . ' (' . $value['count'] . ')';
                    }
                }
            }            
        }
        return $this->values;
    }

    public function buildFetch( OCClassSearchFormFetcher $fetcher, $requestKey, $requestValue, &$filters )
    {
        if ( $this->contentClassAttribute->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
        {
            $fieldName = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, 'name', 'search' );
            $addQuote = true;
        }
        else
        {
            $fieldName = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, null, 'search' );
            $addQuote = false;
        }
        if ( is_array( $requestValue ) )
        {
            $values = array();
            foreach( $requestValue as $v )
            {
                $values[] = $fieldName . ':' . $fetcher->encode( $v, $addQuote );
            }
            $filters[] = array( 'or', $values );
        }
        else
        {
            $filters[] = $fieldName . ':' . $fetcher->encode( $requestValue, $addQuote );
        }

        $fetcher->addFetchField( array(
            'name' => $this->contentClassAttribute->attribute( 'name' ),
            'value' => $requestValue,
            'remove_view_parameters' => $fetcher->getViewParametersString( array( $requestKey ) )
        ));
    }
}

?>