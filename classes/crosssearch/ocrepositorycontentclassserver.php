<?php

class OCRepositoryContentClassServer implements OCRepositoryServerInterface
{
    protected $classIdentifier;
    
    protected $baseSearchParameters = array( 'subtree_array' => array( 1 ),
                                             'limit' => 10,
                                             'as_objects' => false );
    
    public function __construct( $parameters )
    {
        if ( !isset( $parameters['ClassIdentifier'] ) )
        {
            throw new Exception( "Parametro del costruttore 'ClassIdentifier' mancante" );
        }
        $this->classIdentifier = $parameters['ClassIdentifier'];
    }
    
    public function run()
    {
        $result = array();
        $http = eZHTTPTool::instance();
        $action = $http->getVariable( 'action', false );
        $parameters = $http->getVariable( 'parameters', false );
        $result['request'] = array(
          'action' => $action,
          'parameters' => $parameters
        );
        $result['response'] = call_user_func( array( $this, $action ), $parameters );
        return $result;
    }
    
    protected function navigationList( $parameters )
    {
        return OCFacetNavgationHelper::navigationList( $parameters, $this->baseSearchParameters );
    }        
    
    protected function urlDecodeArray( &$arr )
    {
        foreach ( array_keys( $arr ) as $key )
        {
            if ( is_array( $arr[$key] ) )
            {
                $this->urlDecodeArray( $arr[$key] );
            }
            else
            {
                $arr[$key] = urldecode( $arr[$key] );
            }
        }
    }
    
    protected function search( $parameters )
    {
        $this->urlDecodeArray( $parameters );        
        $result = OCClassSearchFormHelper::result( $this->baseSearchParameters, $parameters, false );
        return array(
            'fetch_parameters' => $result->attribute( 'fetch_parameters' ),
            'count' => $result->attribute( 'count' ),
            'contents' => $result->attribute( 'contents' ),
            'fields' => $result->attribute( 'fields' )
        );
    }
    
    public function info()
    {
        return array(
          'type' => 'ContentClass',
          'parameters' => array( 'ClassIdentifier' => $this->classIdentifier )
        );
    }

}