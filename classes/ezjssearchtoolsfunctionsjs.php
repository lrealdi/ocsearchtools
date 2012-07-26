<?php


class ezjsSearchToolsFunctionsJS extends ezjscServerFunctions
{
    /**
     * Example function for returning time stamp + first function argument if present
     *
     * @param array $args
     * @return int|string
     */
    public static function time( $args )
    {
        if ( $args && isset( $args[0] ) )
            return htmlspecialchars( $args[0] ) . '_' . time();
        return time();
    }
    
    private static function parseData()
    {
        $http = eZHTTPTool::instance();
        $tpl = eZTemplate::factory();
        
        $nodeID = $http->postVariable( 'nodeID', 0 );
        
        $subtree = explode( '::', $http->postVariable( 'subtree', array() ) );
        if ( empty( $subtree ) )
        {
            $subtree = array( $nodeID );
        }
        
        $classes = explode( '::', $http->postVariable( 'classes', array() ) );
        
        $facets = array();
        $tmpFacets = $http->postVariable( 'facets', '' );
        $tmpFacets = explode( '::', $tmpFacets );
        foreach( $tmpFacets as $tmpFacet )
        {
            $tmpFacet = explode( ';' , $tmpFacet );
            $facets[] = array( 'field' => $tmpFacet[0],
                               'name' => $tmpFacet[1],
                               'limit' => $tmpFacet[2]
                             );
        }
        
        $viewParameters = array();
        $tmpViewParameters = $http->postVariable( 'view_parameters', '' );
        $tmpViewParameters = explode( ';' , $tmpViewParameters );
        foreach( $tmpViewParameters as $tmpViewParameter )
        {
            $tmpViewParameter = explode( '::', $tmpViewParameter );
            if ( isset( $tmpViewParameter[1] ) )
            {
                $viewParameters[$tmpViewParameter[0]] = urldecode( $tmpViewParameter[1] );
            }
        }
        
        $tpl->setVariable( 'nodeID', $nodeID );
        $tpl->setVariable( 'facets', $facets );
        $tpl->setVariable( 'classes', $classes );
        $tpl->setVariable( 'view_parameters', $viewParameters );
        return $tpl;
    }
    
    public static function facet_search()
    {
        $tpl = self::parseData();
        $result = $tpl->fetch( 'design:ajax/facet_search_result.tpl' );
        $select = $tpl->fetch( 'design:ajax/facet_search_select.tpl' );
        return array( 'result' => $result, 'select' => $select );
    }
    
    public static function facet_search_result()
    {        
        $tpl = self::parseData();
        $template = $tpl->fetch( 'design:ajax/facet_search_result.tpl' );
        return $template;
    }

    public static function facet_search_select()
    {        
        $tpl = self::parseData();
        $template = $tpl->fetch( 'design:ajax/facet_search_select.tpl' );
        return $template;
    }
    
}

?>
