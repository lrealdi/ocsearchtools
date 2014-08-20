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
                               'limit' => $tmpFacet[2],
                               'sort' => isset( $tmpFacet[3] ) ? $tmpFacet[3] : 'count'
                             );
        }
        
        $defaultFilters = array();
        $tmpDefaultFilters = $http->postVariable( 'default_filters', false );
        if ( $tmpDefaultFilters )
            $defaultFilters = explode( ';', $tmpDefaultFilters );
        
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
        
        $useDateFilter = $http->postVariable( 'use_date_filter', 0 );
        
        if ( ( isset( $viewParameters['dateFilter'] ) && $viewParameters['dateFilter'] > 6 ) || ( $useDateFilter == 0 ) ) 
        {
            $viewParameters['dateFilter'] = 0;
        }
        
        $tpl->setVariable( 'useDateFilter', $useDateFilter );
        $tpl->setVariable( 'nodeID', $nodeID );
        $tpl->setVariable( 'facets', $facets );        
        $tpl->setVariable( 'default_filters', $defaultFilters );
        $tpl->setVariable( 'classes', $classes );
        $tpl->setVariable( 'subtree', $subtree );        
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
    
    public static function facetnavigation()
    {
        $http = eZHTTPTool::instance();
        $json = $http->postVariable( 'json', null );
        $fetchParams = json_decode( $json, true );
        $userParameters = $http->postVariable( 'userParameters', null );

        foreach( $userParameters as $key => $value )
            if ( empty( $value ) )
                unset( $userParameters[$key] );

        $token = $http->postVariable( 'token', null );
        $template = $http->postVariable( 'template', null );    
        if ( OCFacetNavgationHelper::validateToken( $token, $fetchParams ) )
        {
            $data = OCFacetNavgationHelper::data( $fetchParams, $userParameters, '', isset( $userParameters['query'] ) ? $userParameters['query'] : '' );
            $contentTpl = $template['content'];
            $navigationTpl = $template['navigation'];
            
            $tpl = eZTemplate::factory();
            $tpl->setVariable( 'data', $data );
            $tpl->setVariable( 'view_parameters', $userParameters );            
            if ( is_array( $contentTpl ) )
            {                
                foreach( $contentTpl as $key => $value )
                    if ( $key != 'name' )
                        $tpl->setVariable( $key, $value );
                $content = $tpl->fetch( 'design:' . $contentTpl['name'] );
            }
            else
                $content = $tpl->fetch( 'design:' . $contentTpl );

            $tpl = eZTemplate::factory();
            $tpl->setVariable( 'data', $data );
            if ( is_array( $navigationTpl ) )
            {                
                foreach( $navigationTpl as $key => $value )
                    if ( $key != 'name' )
                        $tpl->setVariable( $key, $value );
                $navigation = $tpl->fetch( 'design:' . $navigationTpl['name'] );
            }
            else
                $navigation = $tpl->fetch( 'design:' . $navigationTpl );

            return array(
                'content' => $content,
                'navigation' => $navigation
                //'fetch_paramters' => $data['fetch_parameters'],
            );
        }
        else
        {
            throw new Exception( "Invalid token" );
        }
    }
    
}

?>
