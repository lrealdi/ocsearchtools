<?php

class OCClassSearchFormFetcher
{
    
    protected static $_result;

    protected $requestFields = array();

    protected $fetchFields = array();

    protected $isFetch = false;

    protected $baseParameters = array();
    
    public $protectedFields = array( 'RedirectNodeID', 'RedirectUrlAlias' );
    
    public $searchText = '';
    
    public function requestField( $key )
    {
        if ( isset( $this->requestFields[$key] ) )
        {
            return $this->requestFields[$key];
        }
        return false;
    }
    
    public function setBaseParameters( $baseParameters )
    {
        $this->baseParameters = $baseParameters;
    }
    
    public function setRequestFields( $requestFields, $parseViewParameter = false )
    {
        if ( $parseViewParameter )
        {            
            foreach( $requestFields as $key => $value )
            {
                $value = explode( '::', $value );
                if ( count( $value ) < 2 )
                {
                    $value = $value[0];
                }
                $this->requestFields[$key] = $value;
            }
        }
        else
        {
            $this->requestFields = $requestFields;
        }
    }
    
    public function getViewParametersString( $removeItems = array() )
    {
        $string = '';
        foreach( $this->requestFields as $key => $value )
        {
            if ( $key != ''
                 && $value != ''
                 && !in_array( $key, $removeItems )
                 && !in_array( $key, $this->protectedFields ) )
            {
                if ( is_string( $value ) )
                {
                    $string .= "/({$key})/{$value}";
                }
                elseif( is_array( $value ) )
                {
                    $value = implode( '::', $value );
                    $string .= "/({$key})/{$value}";
                }
            }
        }
        return $string;
    }
    
    public function encode( $value, $addQuote )
    {
        $value = addcslashes( $value, '"' );
        if ( $addQuote )
            return '"' . $value . '"';
        return $value;
    }

    public function addFetchField( $fieldArray )
    {
        foreach( $this->fetchFields as $field )
        {
            if ( $field['name'] == $fieldArray['name']
                 && $field['value'] == $fieldArray['value']
                 && $field['remove_view_parameters'] == $fieldArray['remove_view_parameters'] )
            {
                return false;
            }
        }
        $this->fetchFields[] = $fieldArray;
    }
    
    public function buildFetch()
    {
        $filters = array();
        foreach( $this->requestFields as $key => $value )
        {
            if ( strpos( $key, OCClassSearchFormAttributeField::NAME_PREFIX ) !== false )
            {
                $contentClassAttributeID = str_replace( OCClassSearchFormAttributeField::NAME_PREFIX, '', $key );
                $contentClassAttribute = eZContentClassAttribute::fetch( $contentClassAttributeID );
                if ( $contentClassAttribute instanceof eZContentClassAttribute )
                {
                    $field = OCClassSearchFormAttributeField::instance( $contentClassAttribute );
                    $this->isFetch = true;
                    $field->buildFetch( $this, $key, $value, $filters );
                }
            }
            elseif ( in_array( $key, OCFacetNavgationHelper::$allowedUserParamters ) )
            {
                if ( !empty( $value ) )
                {
                    $this->baseParameters[$key] = $value;
                    $this->isFetch = true;
                }
            }
            elseif ( $key == 'class_id' )
            {
                $this->baseParameters[$key] = $value;
                $this->addFetchField( array(
                    'name' => ezpI18n::tr( 'extension/ezfind/facets', 'Content type' ),
                    'value' => eZContentClass::fetch( $value )->attribute( 'name' ),
                    'remove_view_parameters' => $this->getViewParametersString( array( $key ) )
                ) );
                $this->isFetch = true;
            }
            elseif ( $key == 'query' )
            {
                $this->searchText = $value;
                $this->isFetch = true;
            }                        
        }
        $this->baseParameters['filter'] = $filters;
        return $this->baseParameters;
    }
    
    public function isFetch()
    {
        $this->buildFetch();
        return $this->isFetch;
    }
    
    protected function fetch()
    {        
        if ( self::$_result == null )
        {
            if ( $this->isFetch() )
            {
                $params = OCFacetNavgationHelper::map( $this->baseParameters );                
                self::$_result = OCFacetNavgationHelper::fetch( $params, $this->searchText );
            }
            else
                self::$_result = false;
        }
        return self::$_result;
    }

    public function searchCount()
    {
        $data = $this->fetch();
        if ( $data ) return $data['SearchCount'];
        return 0;
    }
    
    public function searchResult()
    {
        $data = $this->fetch();
        if ( $data ) return $data['SearchResult'];
        return array();
    }
    
    
    public function attribute( $name )
    {
        switch( $name )
        {
            case 'fields':
                return $this->fetchFields;
            break;
            
            case 'count':
                return $this->searchCount();
            break;
        
            case 'contents':
                return $this->searchResult();
            break;
        
            case 'is_search_request':
                return $this->isFetch();
            break;
        
            case 'fetch_parameters':
                return $this->buildFetch();
            break;
        }
    }

    public function attributes()
    {
        return array( 'fields', 'contents', 'count', 'fetch_parameters', 'is_search_request' );
    }

    public function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }
    
}

?>