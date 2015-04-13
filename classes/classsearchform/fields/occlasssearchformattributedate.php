<?php

class OCClassSearchFormAttributeDate extends OCClassSearchFormAttributeField
{
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
                $range = $this->getRangeFromValue( $v );
                if ( $range !== null )
                {                
                    $values[] = $fieldName . ':[' . ezfSolrDocumentFieldBase::preProcessValue( $range->startDateTime->getTimestamp(), 'date' ) . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $range->endDateTime->getTimestamp(), 'date' ) . ']';
                }                
            }
            $filters[] = $values;
        }
        else
        {                            
            $range = $this->getRangeFromValue( $requestValue );
            if ( $range !== null )
            {                
                $filters[] = $fieldName . ':[' . ezfSolrDocumentFieldBase::preProcessValue( $range->startDateTime->getTimestamp(), 'date' ) . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $range->endDateTime->getTimestamp(), 'date' ) . ']';
            }
        }
    
        $fetcher->addFetchField( array(
            'name' => $this->contentClassAttribute->attribute( 'name' ),
            'value' => $requestValue,
            'remove_view_parameters' => $fetcher->getViewParametersString( array( $requestKey ) )
        ));
    }
    
    protected function getRangeFromValue( $requestValue )
    {
        $return = null;
        $startDateTime = DateTime::createFromFormat( OCCalendarData::PICKER_DATE_FORMAT, $requestValue , OCCalendarData::timezone() );                
        if ( $startDateTime instanceof DateTime )
        {
            $startDateTime->setTime( 00, 00 );
            $endDateTime = clone $startDateTime;
            $endDateTime->setTime( 23, 59 );
            $return = new stdClass();
            $return->startDateTime = $startDateTime;
            $return->endDateTime = $endDateTime;
        }
        return $return;
    }
}