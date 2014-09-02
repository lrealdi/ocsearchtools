<?php

class OCRemoteClassSearchFormAttributeField extends OCClassSearchFormField
{
    const NAME_PREFIX = 'attribute';

    /**
     * @var OCRemoteClassSearchFormAttributeField[]
     */
    protected static $_instances = array();

    /**
     * @var array
     */
    protected $values;

    /**
     * @var OCRepositoryContentClassClient
     */
    protected $client;

    /**
     * @var eZContentClassAttribute
     */
    public $contentClassAttribute;

    /**
     * @param eZContentClassAttribute $attribute
     * @param stdClass $remoteDefinition
     * @param OCRepositoryContentClassClient $client
     */
    protected function __construct( eZContentClassAttribute $attribute, stdClass $remoteDefinition, OCRepositoryContentClassClient $client )
    {        
        $this->contentClassAttribute = $attribute;
        $this->attributes = array(
           'id' => $attribute->attribute( 'id' ), 
           'name' => self::NAME_PREFIX . $remoteDefinition->ID, 
           'value' => '', //@todo? 
           'class_attribute' => $attribute           
        );        
        $this->functionAttributes = array( 'values' => 'getValues' );
        $this->client = $client;
    }

    /**
     * @param eZContentClassAttribute $attribute
     * @param stdClass $remoteDefinition
     * @param OCRepositoryContentClassClient $client
     *
     * @return mixed
     */
    public static function instance( eZContentClassAttribute $attribute, stdClass $remoteDefinition, OCRepositoryContentClassClient $client )
    {
        if ( !isset( self::$_instances[$attribute->attribute( 'id' )] ) )
        {
            self::$_instances[$attribute->attribute( 'id' )] = new OCRemoteClassSearchFormAttributeField( $attribute, $remoteDefinition, $client );
        }
        return self::$_instances[$attribute->attribute( 'id' )];
    }

    /**
     * @return array
     */
    protected function getValues()
    {        
        if ( $this->values === null )
        {
            $this->values = array();
            if ( $this->contentClassAttribute->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
            {                
                //$field = ezfSolrDocumentFieldBase::generateSubattributeFieldName( $this->contentClassAttribute, 'name', 'string' );
                
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
                $field = ezfSolrDocumentFieldBase::generateAttributeFieldName( $this->contentClassAttribute, 'string' );
            }
            
            $facets = array( 'field' => $field, 'name'=> $this->attributes['name'], 'limit' => 300, 'sort' => 'alpha' );
                                    
            $fetchParameters = array( 'SearchContentClassID' => array( $this->contentClassAttribute->attribute( 'contentclass_id' ) ),
                                      'Facet' => array( $facets ) );
                       
            $data = $this->client->fetchRemoteNavigationList( $fetchParameters );

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
}

?>