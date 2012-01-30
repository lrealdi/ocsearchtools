<?php

class ocSolrDocumentFieldDate extends ezfSolrDocumentFieldBase
{

    function __construct( eZContentObjectAttribute $attribute )
    {
        parent::__construct( $attribute );
    }

    public function getData()
    {
        $contentClassAttribute = $this->ContentObjectAttribute->attribute( 'contentclass_attribute' );
        $fieldNameArray = array();
        foreach ( array_keys( eZSolr::$fieldTypeContexts ) as $context )
        {
            $fieldNameArray[] = self::getFieldName( $contentClassAttribute, null, $context );
        }
        $fieldNameArray = array_unique( $fieldNameArray );

        $metaData = $this->ContentObjectAttribute->metaData();
        if ( $metaData !== NULL )
        {
            $processedMetaDataArray = array();
            if ( is_array( $metaData ) )
            {
                $processedMetaDataArray = array();
                foreach ( $metaData as $value )
                {
                    $processedMetaDataArray[] = $this->preProcessValue( $value,
                                                self::getClassAttributeType( $contentClassAttribute ) );
                }
            }
            else
            {
    
                $processedMetaDataArray[] = $this->preProcessValue( $metaData,
                                                self::getClassAttributeType( $contentClassAttribute ) );
    
            }
            
            $fields = array();
            foreach ( $fieldNameArray as $fieldName )
            {
                    $fields[$fieldName] = $processedMetaDataArray ;
            }
            return $fields;
        }
        return false;
    }
}

?>
