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
            if ( $this->contentClassAttribute->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
            {
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
                
            }            
            else
            {            
                $field = ezfSolrDocumentFieldBase::generateAttributeFieldName( $this->contentClassAttribute, ezfSolrDocumentFieldBase::getClassAttributeType( $this->contentClassAttribute, null, 'search' ) );
            }
            
            $facets = array( 'field' => $field, 'name'=> $this->attributes['name'], 'limit' => 300, 'sort' => 'alpha' );
            
            $fetchParameters = OCClassSearchFormHelper::result()->buildFetch();
            $currentParameters = array();
            $scopeParameters = array_merge(
                array( 'SearchContentClassID' => array( $this->contentClassAttribute->attribute( 'contentclass_id' ) ),
                       'Facet' => array( $facets ) ),
                OCFacetNavgationHelper::map( OCClassSearchFormHelper::result()->getBaseParameters() )
            );
            if ( isset( $fetchParameters['class_id'] ) && $fetchParameters['class_id'] == $this->contentClassAttribute->attribute( 'contentclass_id' ) )
            {
                $currentParameters = array_merge( OCFacetNavgationHelper::map( $fetchParameters ), $scopeParameters );
            }

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
        
        if ( $this->contentClassAttribute->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
        {
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
        }
        elseif ( $this->contentClassAttribute->attribute( 'data_type_string' ) == 'ezdate' || $this->contentClassAttribute->attribute( 'data_type_string' ) == 'ezdatetime' )
        {
            $fieldName = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, null, 'search' );
            if ( is_array( $requestValue ) )
            {
                $values = array( 'or' );
                foreach( $requestValue as $v )
                {
                    $startDateTime = DateTime::createFromFormat( OCCalendarData::PICKER_DATE_FORMAT, $v , OCCalendarData::timezone() );
                    if ( $startDateTime instanceof DateTime )
                    {                        
                        $startDateTime->setTime( 00, 00 );
                        $endDateTime = clone $startDateTime;
                        $endDateTime->setTime( 23, 59 );
                        $values[] = $fieldName . ':[ ' . ezfSolrDocumentFieldBase::preProcessValue( $startDateTime->getTimestamp(), 'date' ) . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $endDateTime->getTimestamp(), 'date' ) . ' ]';
                    }
                }
                $filters[] = $values;
            }
            else
            {                            
                $startDateTime = DateTime::createFromFormat( OCCalendarData::PICKER_DATE_FORMAT, $requestValue , OCCalendarData::timezone() );                
                if ( $startDateTime instanceof DateTime )
                {
                    $startDateTime->setTime( 00, 00 );
                    $endDateTime = clone $startDateTime;
                    $endDateTime->setTime( 23, 59 );
                    $filters[] = $fieldName . ':[' . ezfSolrDocumentFieldBase::preProcessValue( $startDateTime->getTimestamp(), 'date' ) . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $endDateTime->getTimestamp(), 'date' ) . ']';
                }
            }
        }
        else
        {
            $fieldName = ezfSolrDocumentFieldBase::getFieldName( $this->contentClassAttribute, null, 'search' );
            if ( is_array( $requestValue ) )
            {
                $values = array( 'or' );
                foreach( $requestValue as $v )
                {
                    $values[] = $fieldName . ':' . $fetcher->encode( $v, false );
                }
                $filters[] = $values;
            }
            else
            {            
                $filters[] = $fieldName . ':' . $fetcher->encode( $requestValue, false );
            }
        }
        
        $fetcher->addFetchField( array(
            'name' => $this->contentClassAttribute->attribute( 'name' ),
            'value' => $requestValue,
            'remove_view_parameters' => $fetcher->getViewParametersString( array( $requestKey ) )
        ));
    }
}

?>