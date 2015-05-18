<?php

class OCCalendarSearchResultItem extends OCCalendarItem implements ArrayAccess, OCCalendarSearchResultItemInterface
{
    
    protected $rawResult;

    protected $data;

    /**
     * @param array $rawResult
     * @param OCCalendarSearchContext $context
     *
     * @return OCCalendarSearchResultItem
     */
    final public static function instance( array $rawResult, OCCalendarSearchContext $context  )
    {
        $ini = eZINI::instance( 'ocsearchtools.ini' );
        $className = 'OCCalendarSearchResultItem';
        $contextIdentifier = $context->identifier();
        if ( $ini->hasVariable( 'CalendarSearchContext_' . $contextIdentifier, 'SearchResultItem' ) )
        {
            $className = $ini->variable( 'CalendarSearchContext_' . $contextIdentifier, 'SearchResultItem' );
        }
        elseif ( $ini->hasVariable( 'CalendarSearchHandlers', 'SearchResultItem' ) )
        {
            $className = $ini->variable( 'CalendarSearchHandlers', 'SearchResultItem' );
        }
        return new $className( $rawResult );
    }
    
    protected function __construct( array $rawResult )
    {
        $this->rawResult = $rawResult;
        $this->parse();
    }

    protected function parse()
    {
        $urlAlias = '/' . $this->rawResult['main_url_alias'];
        eZURI::transformURI( $urlAlias, false, 'full' );

        $this->data = array(
            'id' => $this->rawResult['id'],
            'name' => $this->rawResult['name'],
            'class_identifier' => $this->rawResult['class_identifier'],
            'main_node_id' => $this->rawResult['main_node_id'],
            'href' => $urlAlias
        );

        $fromDate = self::getDateTime( $this->rawResult['fields']['attr_from_time_dt'] );
        if ( !$fromDate instanceof DateTime )
        {
            throw new Exception( "Value of 'attr_from_time_dt' not a valid date" );
        }
        $this->data['fromDateTime'] = $fromDate;
        $this->data['from'] = $fromDate->getTimestamp();
        $this->data['identifier'] = $fromDate->format( OpenPACalendarData::FULLDAY_IDENTIFIER_FORMAT );

        if ( isset( $this->rawResult['fields']['attr_to_time_dt'] ) )
        {
            $toDate = self::getDateTime( $this->rawResult['fields']['attr_to_time_dt'] );
            if ( !$toDate instanceof DateTime )
            {
                throw new Exception( "Param 'attr_to_time_dt' is not a valid date" );
            }
            if ( $toDate->getTimestamp() == 0 ) // workarpund in caso di eventi (importati) senza data di termine
            {
                $toDate = $this->fakeToTime( $this->data['fromDateTime'] );
            }
        }
        else
        {
            $toDate = $this->fakeToTime( $this->data['fromDateTime'] );
        }
        $this->data['toDateTime'] = $toDate;
        $this->data['to'] = $toDate->getTimestamp();

        $this->data['duration'] = $this->data['to'] - $this->data['from'];

        $this->isValid = $this->isValid();
    }

    public function offsetExists( $offset )
    {
        return isset( $this->data[$offset] );
    }

    public function offsetGet( $offset )
    {
        return $this->data[$offset];
    }

    public function offsetSet( $offset, $value )
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset( $offset )
    {
        unset( $this->data[$offset] );
    }
    
    public function toHash()
    {
        return $this->data;
    }
    
       
}
