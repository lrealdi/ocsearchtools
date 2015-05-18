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
        $this->normalize();
    }
    
    protected function normalize()
    {        
        $urlAlias = '/' . $this->rawResult['main_url_alias'];
        eZURI::transformURI( $urlAlias, false, 'full' );
        
        $this->data = array(
            'id' => $this->rawResult['id'],  
            'name' => $this->rawResult['name'],
            'class_identifier' => $this->rawResult['class_identifier'],
            'main_node_id' => $this->rawResult['main_node_id'],
            'href' => $urlAlias,
            'orario_svolgimento' => trim( preg_replace("/(\r?\n){2,}/", "\n", $this->rawResult['fields']["attr_orario_svolgimento_t"] ), "\n" ),
            'luogo_svolgimento' => trim( $this->rawResult['fields']["attr_luogo_svolgimento_t"], "\n" ),
            'indirizzo' => trim( $this->rawResult['fields']["attr_indirizzo_t"], "\n" ),
            'telefono' => trim( $this->rawResult['fields']["attr_telefono_t"], "\n" ),
            'fax' => trim( $this->rawResult['fields']["attr_fax_t"], "\n" ),
            'email' => trim( $this->rawResult['fields']["attr_email_t"], "\n" ),
            'costi' => trim( $this->rawResult['fields']["attr_costi_t"], "\n" ),
            
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
        
        $this->data['comune'] = array();
        if ( isset( $this->rawResult['fields']["submeta_comune___id____si"] ) )
        {
            foreach( $this->rawResult['fields']["submeta_comune___id____si"] as $index => $id )
            {
                $this->data['comune'][] =  array(
                    'id' =>  $id,
                    'name' =>  $this->rawResult['fields']["subattr_comune___name____s"][$index]
                );
            }
        }
        
        $this->data['tipo_evento'] = array();
        if ( isset( $this->rawResult['fields']["submeta_tipo_evento___id____si"] ) )
        {
            foreach( $this->rawResult['fields']["submeta_tipo_evento___id____si"] as $index => $id )
            {
                $this->data['tipo_evento'][] =  array(
                    'id' =>  $id,
                    'name' =>  $this->rawResult['fields']["subattr_tipo_evento___name____s"][$index]
                );
            }
        }
        
        $this->data['iniziativa'] = array();
        if ( isset( $this->rawResult['fields']["submeta_iniziativa___main_node_id____si"] ) )
        {
            foreach( $this->rawResult['fields']["submeta_iniziativa___main_node_id____si"] as $index => $nodeId )
            {
                $urlAlias = '/content/view/full/' . $nodeId;
                eZURI::transformURI( $urlAlias, false, 'full' );
                $this->data['iniziativa'][] =  array(                    
                    'name' =>  $this->rawResult['fields']["subattr_iniziativa___name____s"][$index],
                    'href' => $urlAlias
                );
            }
        }
        
        $this->data['luogo_della_cultura'] = array();
        if ( isset( $this->rawResult['fields']["submeta_luogo_della_cultura___main_node_id____si"] ) )
        {
            foreach( $this->rawResult['fields']["submeta_luogo_della_cultura___main_node_id____si"] as $index => $nodeId )
            {
                $urlAlias = '/content/view/full/' . $nodeId;
                eZURI::transformURI( $urlAlias, false, 'full' );
                $this->data['luogo_della_cultura'][] =  array(                    
                    'name' =>  $this->rawResult['fields']["subattr_iniziativa___name____s"][$index],
                    'href' => $urlAlias,
                    'indirizzo' => $this->rawResult['fields']["subattr_luogo_della_cultura___indirizzo____s"][$index],
                );
            }
        }
        
        $this->data['utenza_target'] = array();
        if ( isset( $this->rawResult['fields']["submeta_utenza_target___id____si"] ) )
        {
            foreach( $this->rawResult['fields']["submeta_utenza_target___id____si"] as $index => $id )
            {
                $this->data['utenza_target'][] =  array(
                    'id' =>  $id,
                    'name' =>  $this->rawResult['fields']["subattr_utenza_target___name____s"][$index]
                );
            }
        }
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
