<?php

class OCClassSearchFormAttributeField extends OCClassSearchFormField
{
    private static $fieldHandlers;
    
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
        if ( self::$fieldHandlers === null )
        {
            self::$fieldHandlers = array();
            if ( eZINI::instance( 'ocsearchtools.ini' )->hasVariable( 'ClassSearchFormHandlers', 'AttributeHandlers' ) )
                self::$fieldHandlers = eZINI::instance( 'ocsearchtools.ini' )->variable( 'ClassSearchFormHandlers', 'AttributeHandlers' );
        }
        if ( !isset( self::$_instances[$attribute->attribute( 'id' )] ) )
        {
            if ( isset( self::$fieldHandlers[$attribute->attribute( 'data_type_string' )] ) && class_exists( self::$fieldHandlers[$attribute->attribute( 'data_type_string' )] ) )
            {
                $className = self::$fieldHandlers[$attribute->attribute( 'data_type_string' )];                
            }
            else
            {
                $className = 'OCClassSearchFormAttributeField';
            }
            self::$_instances[$attribute->attribute( 'id' )] = new $className( $attribute );
        }
        return self::$_instances[$attribute->attribute( 'id' )];
    }
    
    protected function getValues()
    {        
        if ( $this->values === null )
        {
            $this->values = array();
            
            $field = ezfSolrDocumentFieldBase::generateAttributeFieldName( $this->contentClassAttribute, ezfSolrDocumentFieldBase::getClassAttributeType( $this->contentClassAttribute, null, 'search' ) );
            
            $facets = array( 'field' => $field, 'name'=> $this->attributes['name'], 'limit' => 500, 'sort' => 'alpha' );

            $currentParameters = $baseParameters = array_merge(
                OCClassSearchFormHelper::result()->getBaseParameters(),
                array(
                    'SearchContentClassID' => array( $this->contentClassAttribute->attribute( 'contentclass_id' ) ),
                    'Facet' => array( $facets ),
                    'SearchLimit' => 1
                )
            );
            if ( OCClassSearchFormHelper::result()->isFetch() )
            {
                $currentParameters = array_merge( $currentParameters, OCClassSearchFormHelper::result()->getCurrentParameters() );
            }

            $data = OCFacetNavgationHelper::navigationList( $baseParameters, $currentParameters, OCClassSearchFormHelper::result()->searchText, OCClassSearchFormHelper::result()->isFetch() );

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
        
        $fieldName = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, null, 'search' );
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

?>