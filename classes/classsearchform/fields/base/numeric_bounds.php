<?php
class OCClassSearchFormNumericFieldBounds implements OCClassSearchFormFieldBoundsInterface
{
    const STRING_SEPARATOR = '-';

    /** @var int */
    protected $start;

    /** @var int */
    protected $end;

    public function __construct()
    {
        $this->start = 0;
        $this->end = 0;
    }

    public function attributes()
    {
        return array(
            'start_js',
            'start_solr',
            'end_js',
            'end_solr'
        );
    }

    public function attribute( $key )
    {
        switch( $key )
        {
            case 'start_js':
            case 'start_solr':
                return $this->start;
                break;

            case 'end_js':
            case 'end_solr':
                return $this->end;
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

    public function setStart( $value )
    {        
        if ( $value instanceof eZContentObjectAttribute )
            $this->start = $value->attribute( 'content' );
        else
            $this->start = $value;
    }

    public function setEnd( $value )
    {
        if ( $value instanceof eZContentObjectAttribute )
            $this->end = $value->attribute( 'content' );
        else
            $this->end = $value;
    }

    public function humanString()
    {
        return $this->start . ' → ' . $this->end;
    }

    public function __toString()
    {
        return $this->start . self::STRING_SEPARATOR . $this->end;
    }
}