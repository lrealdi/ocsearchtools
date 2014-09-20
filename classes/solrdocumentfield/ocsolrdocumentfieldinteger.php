<?php

class ocSolrDocumentFieldInteger extends ezfSolrDocumentFieldBase
{

    const DEFAULT_ATTRIBUTE_TYPE = 'sint';
    
    public static function getFieldName( eZContentClassAttribute $classAttribute, $subAttribute = null, $context = 'search' )
    { 
        switch ( $classAttribute->attribute( 'data_type_string' ) )
        {
            case 'ezinteger' :
            {
                return parent::generateAttributeFieldName( $classAttribute, self::getClassAttributeType( $classAttribute, null, $context ) );            
            } break;
     
            default:
            {} break;
        }
    }

    public function getData()
    {
        $contentClassAttribute = $this->ContentObjectAttribute->attribute( 'contentclass_attribute' );
        
        switch ( $contentClassAttribute->attribute( 'data_type_string' ) )
        {   
            case 'ezinteger' :
            {
                $returnArray = array();
                        
                $value = $this->ContentObjectAttribute->metaData();
                
                $fieldName = parent::generateAttributeFieldName( $contentClassAttribute, self::DEFAULT_ATTRIBUTE_TYPE );
                $returnArray[$fieldName] = intval( $value );
                return $returnArray;
          
            } break;
          
            default:
            {} break;
        }
    }
}
?>
