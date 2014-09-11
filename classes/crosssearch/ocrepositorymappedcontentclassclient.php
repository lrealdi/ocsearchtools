<?php

class OCRepositoryMappedContentClassClient extends OCRepositoryContentClassClient
{    
    const ACTION_SYNC_OBJECT = 'repository_proxy_content_class_sync';
    
    const SERVER_CLASSDEFINITION_PATH = '/classtools/definition/';

    /**
     * @var eZContentClass
     */
    protected $contentClass;

    /**
     * @var OCRemoteClassSearchFormAttributeField[]
     */
    protected $attributeFields;

    /**
     * @param $parameters
     * @return void
     * @throws Exception
     */
    public function init( $parameters )
    {
        $this->attributes = $parameters;        
        $this->functionAttributes = array( 'form' => 'getForm',
                                           'results' => 'getResults' );
        
        if ( !isset( $this->attributes['ClassIdentifier'] ) )
        {
            throw new Exception( "Il repository remoto non ha restituito il parametro ClassIdentifier" );
        }
        
        $this->classIdentifier = $this->attributes['ClassIdentifier'];
        $this->remoteContentClassDefinition = $this->getRemoteClassDefinition();
        $this->initClassMap();
        $this->attributes['class'] = $this->contentClass;
    }

    protected function initClassMap()
    {
        $definition = $this->attribute( 'definition' );
        if ( !isset( $definition['LocalClassIdentifier'] ) )
        {
            throw new Exception( "Configurazione LocalClassIdentifier non trovata" );
        }
        if ( !isset( $definition['MapRemoteLocalAttributes'] ) )
        {
            throw new Exception( "Configurazione MapRemoteLocalAttributes non trovata" );
        }
        $mapClassIdentifier = $definition['LocalClassIdentifier'];
        $mapAttributes = $definition['MapRemoteLocalAttributes'];
        $remoteClassAttributes = array_keys( $mapAttributes );
        $localeClassAttributes = array_values( $mapAttributes );

        $contentClassDataMap = array();
        foreach( $remoteClassAttributes as $identifier )
        {
            $contentClassAttribute = new OCClassSearchTemplate();
            $contentClassAttribute->setAttributes(
                array(
                     'id' => $mapClassIdentifier . '-' . $identifier,
                     'identifier' => $identifier,
                     'is_searchable' => true, //@todo
                     'contentclass_id' => $this->classIdentifier
                )
            );
            $contentClassDataMap[$identifier] = $contentClassAttribute;
        }

        $contentClass = new OCClassSearchTemplate();
        $contentClass->setAttributes( array(
            'identifier' => $mapClassIdentifier,
            'data_map' => $contentClassDataMap
        ));
    }

    /**
     * @return string
     */
    protected function getForm()
    {        
        $classKeyArray = array(
            array( 'class_identifier', $this->contentClass->attribute( 'identifier' ) )
        );
        
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'class', $this->contentClass );
        $tpl->setVariable( 'remote_class_id', $this->remoteContentClassDefinition->ID );
        $tpl->setVariable( 'client', $this );
        
        $attributeFields = array();
        $dataMap = $this->contentClass->attribute( 'data_map' );

        $disabled = array();
        if ( eZINI::instance( 'ocsearchtools.ini' )->hasVariable( 'RemoteClassSearchFormSettings', 'DisabledAttributes' ) )
        {
            $disabled = eZINI::instance( 'ocsearchtools.ini' )->variable( 'RemoteClassSearchFormSettings', 'DisabledAttributes' );    
        }

        /** @var $dataMap eZContentClassAttribute[] */
        foreach( $dataMap as $attribute )
        {
            if ( !in_array( $this->contentClass->attribute( 'identifier' ) . '/' . $attribute->attribute( 'identifier' ), $disabled )
                 && $attribute->attribute( 'is_searchable' ) )
            {
                if ( isset( $this->remoteContentClassDefinition->DataMap[0]->{$attribute->attribute( 'identifier' )} ) )
                {

                    $attribute->setAttribute(
                        'data_type_string',
                        $this->remoteContentClassDefinition->DataMap[0]->{$attribute->attribute( 'identifier' )}->DataTypeString
                    );
                    $inputField = OCRemoteClassSearchFormAttributeField::instance( $attribute,
                                                                                   $this->remoteContentClassDefinition->DataMap[0]->{$attribute->attribute( 'identifier' )},
                                                                                   $this);
                    
                    $keyArray = array(
                        array( 'class_identifier', $this->contentClass->attribute( 'identifier' ) ),
                        array( 'attribute_identifier', $inputField->contentClassAttribute->attribute( 'identifier' ) )
                    );

                    $tpl = eZTemplate::factory();
                    $tpl->setVariable( 'class', $this->contentClass );
                    $tpl->setVariable( 'attribute', $inputField->contentClassAttribute );
                    $tpl->setVariable( 'input', $inputField );                    
                    
                    $res = eZTemplateDesignResource::instance();
                    $res->setKeys( $keyArray );        
                    
                    $templateName = $inputField->contentClassAttribute->attribute( 'data_type_string' );
                    
                    $attributeFields[$inputField->attribute( 'id' )] = $tpl->fetch( 'design:class_search_form/datatypes/' . $templateName . '.tpl' );
                }
            }
        }
        
        $tpl->setVariable( 'attribute_fields', $attributeFields );
        $parameters = array( 'action' => 'search' );
        $tpl->setVariable( 'parameters', $parameters );
        $formAction = $this->attributes['definition']['ClientBasePath'];
        eZURI::transformURI( $formAction );
        $tpl->setVariable( 'form_action', $formAction );

        $res = eZTemplateDesignResource::instance();
        $res->setKeys( $classKeyArray );
        
        return $tpl->fetch( 'design:repository/contentclass_client/remote_class_search_form.tpl' );   
    }

    /**
     * @return string
     */
    public function templateName()
    {
        return 'design:repository/contentclass_client/client.tpl';
    }

    protected function checkClass( $createClassIfNotExists = false )
    {        
        return true;
    }

    /**
     * @param int $remoteNodeID
     * @param int $localParentNodeID
     *
     * @return eZContentObject
     * @throws Exception
     */
    public function import( $remoteNodeID, $localParentNodeID )
    {
        if ( !class_exists( 'OCOpenDataApiNode' ) )
        {
            throw new Exception( "Libreria OCOpenDataApiNode non trovata" );
        }
        $apiNodeUrl = rtrim( $this->attributes['definition']['Url'], '/' ) . '/api/opendata/v1/content/node/' . $remoteNodeID;
        $remoteApiNode = OCOpenDataApiNode::fromLink( $apiNodeUrl );
        if ( !$remoteApiNode instanceof OCOpenDataApiNode )
        {
            throw new Exception( "Url remoto \"{$apiNodeUrl}\" non raggiungibile" );
        }
        $newObject = $remoteApiNode->createContentObject( $localParentNodeID );
        if ( !$newObject instanceof eZContentObject )
        {            
            throw new Exception( "Fallita la creazione dell'oggetto da nodo remoto" );
        }
        $rowPending = array(
            'action'        => self::ACTION_SYNC_OBJECT,            
            'param'         => $newObject->attribute( 'id' )
        );        
        $pendingItem = new eZPendingActions( $rowPending );
        $pendingItem->store();
        return $newObject;
    }

}