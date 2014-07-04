<?php

class OCClassSearchFormHelper
{    
    protected static $_instances = array();
    
    protected static $_result;
    
    protected $contentClass;
    
    protected $attributeFields;
    
    public static function redirect( array $requestFields, eZModule $module = null )
    {        
        $result = new OCClassSearchFormResult();
        $result->setRequestFields( $requestFields );
        
        if ( $module )
        {
            $redirect = '/';
            if ( isset( $requestFields['RedirectUrlAlias'] ) )
            {
                $redirect = $requestFields['RedirectUrlAlias'];
            }
            elseif ( isset( $requestFields['RedirectNodeID'] ) )
            {
                $node = eZContentObjectTreeNode::fetch( $requestFields['RedirectNodeID'] );
                if ( $node instanceof eZContentObjectTreeNode )
                {
                    $redirect = $node->attribute( 'url_alias' );
                }
            }
            
            $redirect = rtrim( $redirect, '/' ) . $result->getViewParametersString();            
            $module->redirectTo( $redirect );
        }
                
        self::$_result = $result;
    }
    
    public static function displayForm( $classIdentifier, $parameters )
    {
        $instance = self::instance( $classIdentifier );        
        $keyArray = array( array( 'class', $instance->contentClass->attribute( 'id' ) ),
                           array( 'class_identifier', $instance->contentClass->attribute( 'identifier' ) ),                           
                           array( 'class_group', $instance->contentClass->attribute( 'match_ingroup_id_list' ) ) );
        
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'class', $instance->contentClass );
        $tpl->setVariable( 'helper', $instance );
        $tpl->setVariable( 'parameters', $parameters );
        
        $res = eZTemplateDesignResource::instance();
        $res->setKeys( $keyArray );        
        
        return $tpl->fetch( 'design:class_search_form/class_search_form.tpl' );        
    }
    
    public static function displayAttribute( OCClassSearchFormHelper $instance, OCClassSearchFormAttributeField $field )
    {
        $keyArray = array( array( 'class', $instance->contentClass->attribute( 'id' ) ),
                           array( 'class_identifier', $instance->contentClass->attribute( 'identifier' ) ),                           
                           array( 'class_group', $instance->contentClass->attribute( 'match_ingroup_id_list' ) ),
                           array( 'attribute', $field->contentClassAttribute->attribute( 'id' ) ),                           
                           array( 'attribute_identifier', $field->contentClassAttribute->attribute( 'identifier' ) ) );
        
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'class', $instance->contentClass );
        $tpl->setVariable( 'attribute', $field->contentClassAttribute );
        
        $res = eZTemplateDesignResource::instance();
        $res->setKeys( $keyArray );        
        
        $templateName = $field->contentClassAttribute->attribute( 'data_type_string' );
        
        return $tpl->fetch( 'design:class_search_form/datatypes/' . $templateName . '.tpl' );              
    }
    
    public static function result( $baseParameters = array(), $requestFields = array(), $parseViewParameter = false )
    {
        if ( self::$_result === null )
        {            
            $result = new OCClassSearchFormResult();
            $result->setBaseParameters( $baseParameters );
            $result->setRequestFields( $requestFields, $parseViewParameter );
            self::$_result = $result;
        }
        return self::$_result;
    }
    
    public static function instance( $classIdentifier )
    {
        if ( !isset( self::$_instances[$classIdentifier] ) )
        {
            self::$_instances[$classIdentifier] = new OCClassSearchFormHelper( $classIdentifier );
        }
        return self::$_instances[$classIdentifier];
    }
    
    protected function __construct( $classIdentifier )
    {        
        $this->contentClass = eZContentClass::fetchByIdentifier( $classIdentifier );
        if ( !$this->contentClass instanceof eZContentClass )
        {
            throw new Exception( "Class $classIdentifier not found" );
        }
    }
    
    public function attributeFields()
    {
        if ( $this->attributeFields === null )
        {
            $this->attributeFields = array();
            $dataMap = $this->contentClass->attribute( 'data_map' );
            $disabled = eZINI::instance( 'ocsearchtools.ini' )->variable( 'ClassSearchFormSettings', 'DisabledAttributes' );
            foreach( $dataMap as $attribute )
            {
                if ( !in_array( $this->contentClass->attribute( 'identifier' ) . '/' . $attribute->attribute( 'identifier' ), $disabled )
                     && $attribute->attribute( 'is_searchable' ) )
                {
                    $inputField = OCClassSearchFormAttributeField::instance( $attribute );
                    $this->attributeFields[$inputField->attribute( 'id' )] = $inputField;
                }
            }
        }
        return $this->attributeFields;        
    }
    
    public function attribute( $name )
    {
        switch( $name )
        {
            case 'result':
                $result = self::result();                
                return $result;
            break;
        
            case 'attribute_fields':
                return $this->attributeFields();
            break;
        
            case 'query_field':
                return new OCClassSearchFormQueryField( $this );
            break;
        
            case 'sort_field':
                return new OCClassSearchFormSortField( $this );
            break;
        
            case 'published_field':
                return new OCClassSearchFormPublishedField( $this );
            break;
        
            case 'class':
                return $this->contentClass;
            break;
        }
    }

    public function attributes()
    {
        return array( 'result', 'attribute_fields', 'query_field', 'sort_field', 'published_field', 'class' );
    }

    public function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }
}

?>