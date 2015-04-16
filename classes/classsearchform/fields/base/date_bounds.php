<?php
class OCClassSearchFormDateFieldBounds implements OCClassSearchFormFieldBoundsInterface
{
    const STRING_SEPARATOR = '-';

    protected $start;
    protected $end;

    public function __construct()
    {
        $this->start = new DateTime();
        $this->end = new DateTime();
    }

    public function attributes()
    {
        return array(
            'start_timestamp',
            'start_js',
            'start_solr',
            'end_timestamp',
            'end_js',
            'end_solr'
        );
    }

    public function attribute( $key )
    {
        switch( $key )
        {
            case 'start_timestamp':
                return $this->start->format( 'U' );
                break;
            case 'start_js':
                return $this->start->format( 'U' ) * 1000;
                break;
            case 'start_solr':
                return ezfSolrDocumentFieldBase::preProcessValue( $this->start->format( 'U' ), 'date' );
                break;
            case 'end_timestamp':
                return $this->end->format( 'U' );
                break;
            case 'end_js':
                return $this->end->format( 'U' ) * 1000;
                break;
            case 'end_solr':
                return ezfSolrDocumentFieldBase::preProcessValue( $this->end->format( 'U' ), 'date' );
                break;
            default: return false;
        }
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }

    public static function fromString( $string )
    {
        $data = new static();
        $values = explode( self::STRING_SEPARATOR, $string );
        if ( count( $values ) == 2 )
        {
            $data->setStart( $values[0] );
            $data->setEnd( $values[1] );
        }
        elseif ( count( $values ) == 1 )
        {
            $data->setStart( $values[0] );
            $data->setEnd( $values[0] );
        }
        return $data;
    }

    public function setStart( $timestamp )
    {
        $this->start->setTimestamp( $timestamp );
        $this->start->setTime( 00, 00 );
    }

    public function setEnd( $timestamp )
    {
        $this->end->setTimestamp( $timestamp );
        $this->end->setTime( 23, 59 );
    }

    public function humanString()
    {
        return $this->start->format( OCCalendarData::PICKER_DATE_FORMAT ) . ' → ' . $this->end->format( OCCalendarData::PICKER_DATE_FORMAT );
    }

    public function __toString()
    {
        return $this->start->format( 'U' ) . self::STRING_SEPARATOR . $this->end->format( 'U' );
    }
}