<?php

class SearchFormOperator
{
    static $Operators = array(
        'setFilterParameter',
        'removeFilterParameter',
        'getFilterParameter',
        'getFilterParameters',
        'getFilterUrlSuffix',
        'in_array_r',
        'sort',
        'asort',
        'addQuoteOnFilter',
        'parsedate',
        'facet_navigation',
        'calendar'
    );
    public static $filters = array();
    public static $query_filters = array();
    
    function SearchFormOperator()
    {
    }

    /*!
    Return an array with the template operator name.
    */
    function operatorList()
    {
        return self::$Operators;
    }
    /*!
     \return true to tell the template engine that the parameter list exists per operator type,
             this is needed for operator classes that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }
    /*!
     See eZTemplateOperator::namedParameterList
    */
    function namedParameterList()
    {
        return array(
            'setFilterParameter' => array(
                'name' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'value' => array(
                    'type' => 'mixed',
                    'required' => true
                )                
            ),
            'getFilterParameter' => array(
                'name' => array(
                    'type' => 'string',
                    'required' => true
                )                
            ),
            'removeFilterParameter' => array(
                'name' => array(
                    'type' => 'string',
                    'required' => true
                )                
            ),
            'getFilterParameters' => array(
                'as_array' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => false
                ),
                'cond' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => false
                )
            ),
            'getFilterUrlSuffix' => array(),
            'in_array_r' => array(
                'needle' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'haystack' => array(
                    'type' => 'array',
                    'required' => true
                )
            ),
            'addQuoteOnFilter' => array(),
            'sort' => array(),
            'asort' => array(),
            'parsedate' => array(),
            'parsedate' => array(),
            'facet_navigation' => array(                
                'base_query' => array(
                    'type' => 'array',
                    'required' => true
                ),
                'override' => array(
                    'type' => 'array',
                    'required' => false
                ),
                'base_uri' => array(
                    'type' => 'string',
                    'required' => true
                )
            ),
            'calendar' => array(
                'node' => array(
                    'type' => 'object',
                    'required' => true
                ),
                'parameters' => array(
                    'type' => 'array',
                    'required' => false
                )
            )
        );
    }
    
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
		
        switch ( $operatorName )
        {
            case 'calendar':
            {
                $data = new OpenPACalendarData( $namedParameters['node'] );
                $data->setParameters( $namedParameters['parameters'] );
                $data->fetch();
                $operatorValue = $data->data;
            } break;

            case 'facet_navigation':
            {
                $operatorValue = OCFacetNavgationHelper::data( $namedParameters['base_query'], $namedParameters['override'], $namedParameters['base_uri'] );                
            } break;
            
            case 'setFilterParameter':
            {
                self::$filters[$namedParameters['name']][] = $namedParameters['value'];
                eZDebug::writeNotice($namedParameters, 'setFilterParameter');
                $operatorValue = self::$filters[$namedParameters['name']];
            }break;
            
            case 'getFilterParameter':
            {
                $this->getAllFilters();
                
                if ( isset( self::$query_filters[$namedParameters['name']] ))
                {
                    eZDebug::writeNotice( $namedParameters['name'] .': ' . var_export( self::$query_filters[$namedParameters['name']], true ), 'getFilterParameter');
                    $operatorValue = is_array( self::$query_filters[$namedParameters['name']] ) ? self::$query_filters[$namedParameters['name']] : array( self::$query_filters[$namedParameters['name']] );                    
                    return true;
                }
                $operatorValue = array();
                return false;
            }break;

            case 'removeFilterParameter':
            {
                $this->getAllFilters();
                
                if ( isset( self::$query_filters[$namedParameters['name']] ))
                {
                    self::$query_filters[$namedParameters['name']] = array();
                    return true;
                }
                return false;
            }break;
            
            case 'getFilterParameters':
            {
                $http = eZHTTPTool::instance();
                $this->getAllFilters();
                $filterList = self::$query_filters;
                if ( $namedParameters['as_array'] )
                {
                    $operatorValue = $filterList;
                    return;
                }
                $filterSearchHash = array();
                if ( $namedParameters['cond'] )
                {
                    $filterSearchHash[] = $namedParameters['cond'];
                }
                foreach( $filterList as $name => $value )
                {
                    if ( count($value) > 1 )
                    {
                        $temp_array = array( 'or' );
                        foreach ( $value as $v )
                        {
                            $temp_array[] = $name . ':' . $v;
                        }
                        $filterSearchHash[] = $temp_array;
                    }
                    else
                    {                        
                        $filterSearchHash[] = $name . ':' . $value[0];
                    }
                }
                eZDebug::writeNotice( $filterSearchHash, 'getFilterParameters' );
                $operatorValue = $filterSearchHash;
            } break;
			
            case 'getFilterUrlSuffix':
            {
                $filterSearchHash = $operatorValue;
                $urlSuffix = '';
                $tempArray = array();
                foreach( $filterSearchHash as $filter )
                {
                    if ( is_array( $filter ) )
                    {
                        foreach( $filter as $f )
                        {
                            if ( ( 'and' != $f ) and ( 'or' != $f ) )
                            {
                                if ( !in_array( $f, $tempArray ) )
                                {
                                    $tempArray[] = $f;
                                    $urlSuffix .= '&filter[]=' . $f;
                                }
                            }
                        }
                    }
                    else
                    {
                        if ( !in_array( $filter, $tempArray ) )
                        {                        
                            $tempArray[] = $filter;
                            $urlSuffix .= '&filter[]=' . $filter;
                        }
                    }
                }
                $operatorValue = $urlSuffix;
                
            } break;
            
            case 'addQuoteOnFilter':
            {
                $tempVar = $operatorValue;
                list( $name, $value ) = explode( ':', $tempVar );
                $operatorValue = $name . ':"'. $value . '"';                
            } break;
            
            case 'in_array_r':
            {                
                if ( $this->recursiveArraySearch( $namedParameters['needle'], $namedParameters['haystack'] ) !== false )
                {
                    $operatorValue = true;                    
                    return true;
                }
                $operatorValue = false;                
                return false;
                
            } break;
            
            case 'sort':
            {
                sort( $operatorValue );                
            }break;

            case 'asort':
            {
                eZDebug::writeNotice($operatorValue,__METHOD__);
                asort( $operatorValue );                
            }break;
            
            case 'parsedate':
            {
                $operatorValue = str_replace('"', '', $operatorValue );
                if ( DateTime::createFromFormat( "Y-m-d\TH:i:sP", $operatorValue ) )
                {
                    $operatorValue = DateTime::createFromFormat( "Y-m-d\TH:i:sP", $operatorValue )->format ("U");
                }
                else
                {
                    eZDebug::writeError( $operatorValue . ': ' . var_export( DateTime::getLastErrors(), 1 ), 'parsedate' );
                    $operatorValue = 0;
                    
                }
            }break;
            
            default:
            break;
        }
    }
    
    private function recursiveArraySearch($needle, $haystack, $index = null)
    {
        if ( is_array($haystack) )
        {
            $aIt = new RecursiveArrayIterator($haystack);
            $it  = new RecursiveIteratorIterator($aIt);
           
            while($it->valid())
            {       
                if (((isset($index) AND ($it->key() == $index)) OR (!isset($index))) AND ($it->current() == $needle))
                {
                    return $aIt->key();
                }
               
                $it->next();
            }
        }
       
        return false;
    }
    
    private function getAllFilters()
    {
        $http = eZHTTPTool::instance();
        $filterList = array();
        if ( $http->hasGetVariable( 'filter' ) )
        {
            foreach( $http->getVariable( 'filter' ) as $key => $filterCond )
            {
                if ( $filterCond == '' ) continue;
                
                if ( !is_integer( $key ) )
                {
                    if ( !empty( $filterCond ) )
                    {
                        if ( is_array($filterCond) )
                        {
                            foreach( $filterCond as $fC)
                            {
                                $filterList[$key][] = $fC;
                            }
                        }
                        else
                        {
                            $filterList[$key][] = $filterCond;
                        }
                    }
                }
                else
                {
                    $filterCondParts = explode( ':', $filterCond );
                    if ( !empty( $filterCondParts[1] ) )
                    {
                        $name = array_shift( $filterCondParts );
                        $filterList[$name][] = implode( ':', $filterCondParts );
                    }
                }
            }
        }                

        foreach( self::$filters as $name => $filterArray )
        {
            if ( isset( $filterList[$name] ) )
                $filterList[$name] = array_merge($filterList[$name], self::$filters[$name]);
            else
                $filterList[$name] = self::$filters[$name];
        }
        
        self::$query_filters = $filterList;
    }
    
}

?>