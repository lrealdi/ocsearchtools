<?php

class OCClassSearchFormHelper
{    
    protected static $_instances = array();
    
    protected static $_result;
    
    protected $contentClass;
    
    protected $attributeFields;

    /**
     * Trasforma le variabili $_GET in view_parameters e redirige la richiesta in base al parametro $_GET['RedirectUrlAlias']
     *
     * @see modules/ocsearch/action.php
     * @param array $requestFields
     * @param eZModule $module
     */
    public static function redirect( array $requestFields, eZModule $module = null )
    {        
        $result = new OCClassSearchFormFetcher();
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
    }

    /**
     * Invoca il template 'design:class_search_form/class_search_form.tpl' popolandone le variabili
     *
     * @see SearchFormOperator::modify
     * @param string $classIdentifier
     * @param array $parameters valori impostati da template come input hidden
     *
     * @return array|null|string
     */
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

    /**
     * Invoca il template per il form di attributo
     *
     * @see SearchFormOperator::modify
     * @param OCClassSearchFormHelper $instance
     * @param OCClassSearchFormAttributeField $field
     *
     * @return array|null|string
     */
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

    /**
     * @param array $baseParameters
     * @param array $requestFields
     * @param bool $parseViewParameter
     *
     * @return OCClassSearchFormFetcher
     */
    public static function result( $baseParameters = array(), $requestFields = array(), $parseViewParameter = false )
    {
        if ( self::$_result === null )
        {            
            $result = new OCClassSearchFormFetcher();
            $result->setBaseParameters( $baseParameters );
            $result->setRequestFields( $requestFields, $parseViewParameter );
            self::$_result = $result;
        }
        return self::$_result;
    }

    /**
     * Singleton
     *
     * @param $classIdentifier
     *
     * @return mixed
     */
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

    /**
     * @return OCClassSearchFormAttributeField[]
     */
    public function attributeFields()
    {
        if ( $this->attributeFields === null )
        {
            $this->attributeFields = array();
            $dataMap = $this->contentClass->attribute( 'data_map' );
            $disabled = eZINI::instance( 'ocsearchtools.ini' )->variable( 'ClassSearchFormSettings', 'DisabledAttributes' );
            /** @var $dataMap eZContentClassAttribute[] */
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
        eZDebug::writeError( "Attribute $name not found", __METHOD__ );
        return false;
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