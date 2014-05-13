<?php

class ocSolrDocumentFieldDate extends ezfSolrDocumentFieldBase
{
    
    const DEFAULT_SUBATTRIBUTE_TYPE = 'date';

    function __construct( eZContentObjectAttribute $attribute )
    {
        parent::__construct( $attribute );
    }
    
    public static function getFieldName( eZContentClassAttribute $classAttribute, $subAttribute = null, $context = 'search' )
    { 
        switch ( $classAttribute->attribute( 'data_type_string' ) )
        {
            case 'ezdate':
            case 'ezdatetime':
            {
                if ( $subAttribute and $subAttribute !== '' )
                {
                    // A subattribute was passed
                    return parent::generateSubattributeFieldName( $classAttribute,
                                                                  $subAttribute,
                                                                  self::DEFAULT_SUBATTRIBUTE_TYPE );
                }
                else
                {
                    // return the default field name here.
                    return parent::generateAttributeFieldName( $classAttribute,
                                                               self::getClassAttributeType( $classAttribute, null, $context ) );
                }
            } break;
        
            default:
            break;
        }
    }

    public function getData()
    {
        $contentClassAttribute = $this->ContentObjectAttribute->attribute( 'contentclass_attribute' );
        $metaData = $this->ContentObjectAttribute->metaData();    
        if ( $metaData !== NULL && $metaData > 0 )
        {
            $processedMetaDataArray = array();
            $processedMetaDataArray[] = $this->preProcessValue( $metaData, self::getClassAttributeType( $contentClassAttribute ) );            
            
            $fieldNameArray = array();
            foreach ( array_keys( eZSolr::$fieldTypeContexts ) as $context )
            {
                $fieldNameArray[] = self::getFieldName( $contentClassAttribute, null, $context );
            }
            $fieldNameArray = array_unique( $fieldNameArray );
            
            $fields = array();
            foreach ( $fieldNameArray as $fieldName )
            {
                $fields[$fieldName] = $processedMetaDataArray ;
            }
            
            $year = date( "Y", $metaData );
            $month = date( "n", $metaData );
            $fieldNameArray = array();
            foreach ( array_keys( eZSolr::$fieldTypeContexts ) as $context )
            {
                $fieldNameArray[] = self::getFieldName( $contentClassAttribute, 'year', $context );
            }
            $fieldNameArray = array_unique( $fieldNameArray );
            foreach ( $fieldNameArray as $fieldName )
            {
                $fields[$fieldName] = parent::convertTimestampToDate( strtotime( $year . '-01-01' ) ); ;
            }
            
            $fieldNameArray = array();
            foreach ( array_keys( eZSolr::$fieldTypeContexts ) as $context )
            {
                $fieldNameArray[] = self::getFieldName( $contentClassAttribute, 'yearmonth', $context );
            }
            $fieldNameArray = array_unique( $fieldNameArray );
            foreach ( $fieldNameArray as $fieldName )
            {
                $fields[$fieldName] = parent::convertTimestampToDate( strtotime( $year . '-' . $month . '-01' ) );
            }
            
            
            return $fields;
        }
        return null;
    }
}

?>
