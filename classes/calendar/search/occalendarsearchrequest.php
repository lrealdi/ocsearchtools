<?php

class OCCalendarSearchRequest
{
    /**
     * @var array
     */
    protected $rawRequest;

    /**
     * @var array
     */
    protected $request;

    function __construct( array $request )
    {
        $this->rawRequest = $request;
        $this->parse();
    }

    public function has( $key )
    {
        return isset( $this->request[$key] );
    }

    public function get( $key )
    {
        return $this->request[$key];
    }
    
    public function getRawRequest()
    {
        return $this->rawRequest;
    }

    protected function parse()
    {
        if ( isset( $this->rawRequest['text'] ) )
        {
            $this->request['text'] = $this->rawRequest['text'];
        }

        if ( isset( $this->rawRequest['when'] ) )
        {
            switch ( $this->rawRequest['when'] )
            {
                case 'today':
                {
                    $this->request['when'] = array( new DateTime( 'now' ) );
                } break;

                case 'tomorrow':
                {
                    $this->request['when'] = array( new DateTime( 'tomorrow' ) );
                } break;

                case 'weekend':
                {
                    $currentDate = new DateTime( 'now' );
                    if ( $currentDate->format( 'N' ) == 6 )
                    {
                        $start = clone $currentDate;
                    }
                    else
                    {
                        $start = new DateTime( 'next saturday' );
                    }
                    $end = clone $start;
                    $end->add( new DateInterval( 'P1D' ) );
                    $this->request['when'] = array( $start, $end );
                } break;

                case 'range':
                    if ( isset( $this->rawRequest['dateRange'] ) )
                    {
                        if ( is_array( $this->rawRequest[ 'dateRange' ] ) && count( $this->rawRequest[ 'dateRange' ] ) == 2 )
                        {
                            $start = DateTime::createFromFormat( 'Ymd', $this->rawRequest[ 'dateRange' ][0], new DateTimeZone( "Europe/Rome" ) );
                            $end = DateTime::createFromFormat( 'Ymd', $this->rawRequest[ 'dateRange' ][1], new DateTimeZone( "Europe/Rome" ) );
                            $this->request['when'] = array( $start, $end );
                            $this->request['dateRange'] = $this->rawRequest['dateRange'];
                        }
                    }
                    break;

                default:
                    throw new Exception( "When identifier not handled" );
            }
        }

        if ( isset( $this->rawRequest['what'] ) || isset( $this->rawRequest['_what'] ) )
        {
            $what = array();
            if ( isset( $this->rawRequest['what'] ) )
            {
                $this->rawRequest['what'] = intval( $this->rawRequest['what'] );
                $what[] = $this->rawRequest['what'];
            }
            if ( isset( $this->rawRequest['_what'] ) )
            {
                if ( !is_array( $this->rawRequest['_what'] ) )
                {
                    $this->rawRequest['_what'] = array( $this->rawRequest['_what'] );
                }
                $this->rawRequest['_what'] = array_map( 'intval', $this->rawRequest['_what'] );
                $what = array_merge( $what, $this->rawRequest['_what'] );
            }
            $this->request['what'] = $what;
        }

        if ( isset( $this->rawRequest['where'] ) || isset( $this->rawRequest['_where'] ) )
        {
            $where = array();
            if ( isset( $this->rawRequest['where'] ) )
            {
                $this->rawRequest['where'] = intval( $this->rawRequest['where'] );
                $where[] = $this->rawRequest['where'];
            }
            if ( isset( $this->rawRequest['_where'] ) )
            {
                if ( !is_array( $this->rawRequest['_where'] ) )
                {
                    $this->rawRequest['_where'] = array( $this->rawRequest['_where'] );
                }
                $this->rawRequest['_where'] = array_map( 'intval', $this->rawRequest['_where'] );
                $where = array_merge( $where, $this->rawRequest['_where'] );
            }
            $this->request['where'] = $where;
        }

        if ( isset( $this->rawRequest['target'] ) )
        {
            $this->rawRequest['target'] = array_map( 'intval', $this->rawRequest['target'] );
            $this->request['target'] = $this->rawRequest['target'];
        }

        if ( isset( $this->rawRequest['category'] ) )
        {
            $this->rawRequest['category'] = array_map( 'intval', $this->rawRequest['category'] );
            $this->request['category'] = $this->rawRequest['category'];
        }
    }
}