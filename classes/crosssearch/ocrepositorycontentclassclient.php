<?php

class OCRepositoryContentClassClient extends OCClassSearchTemplate  implements OCRepositoryClientInterface
{    
    const ACTION_SYNC_OBJECT = 'repository_content_class_sync';
    
    const SERVER_CLASSDEFINITION_PATH = '/classtools/definition/';

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $classIdentifier;

    /**
     * @var eZContentClass
     */
    protected $contentClass;

    /**
     * @var OCRemoteClassSearchFormAttributeField[]
     */
    protected $attributeFields;

    /**
     * @var stdClass
     */
    protected $remoteContentClassDefinition;

    /**
     * @var array
     */
    protected $results;

    /**
     * @var string
     */
    protected $currentAction;

    /**
     * @var array
     */
    protected $currentActionParameters;

    /**
     * @param $parameters
     *
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
        $this->checkClass();
        $this->attributes['class'] = $this->contentClass;
        
        $this->remoteContentClassDefinition = $this->getRemoteClassDefinition();
    }

    /**
     * @param string $action
     */
    public function setCurrentAction( $action )
    {
        $this->currentAction = $action;
    }

    /**
     * @param array $parameters
     */
    public function setCurrentActionParameters( $parameters )
    {
        $this->currentActionParameters = $parameters;
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    protected function getRemoteClassDefinition()
    {        
        $serverClassDefinitionUrl = rtrim( $this->attributes['definition']['Url'], '/' ) .  self::SERVER_CLASSDEFINITION_PATH . $this->classIdentifier;

        $currentUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        if ( stripos( $serverClassDefinitionUrl, $currentUrl ) === false )
        {
            $original = json_decode( eZHTTPTool::getDataByURL( $serverClassDefinitionUrl ) );
            if ( !$original )
            {
                throw new Exception( "Definizione remota della classe non raggiungible" );
            }
            if ( isset( $original->error ) )
            {
                throw new Exception( $original->error );
            }
            return $original;             
        }
        throw new Exception( "Server e client coincidono" );
    }

    /**
     * @return string
     */
    protected function getForm()
    {        
        $keyArray = array( array( 'class', $this->contentClass->attribute( 'id' ) ),
                           array( 'class_identifier', $this->contentClass->attribute( 'identifier' ) ),                           
                           array( 'class_group', $this->contentClass->attribute( 'match_ingroup_id_list' ) ) );
        
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
                
                    $inputField = OCRemoteClassSearchFormAttributeField::instance( $attribute,
                                                                                   $this->remoteContentClassDefinition->DataMap[0]->{$attribute->attribute( 'identifier' )},
                                                                                   $this);
                    
                    $keyArray = array( array( 'class', $this->contentClass->attribute( 'id' ) ),
                    array( 'class_identifier', $this->contentClass->attribute( 'identifier' ) ),                           
                    array( 'class_group', $this->contentClass->attribute( 'match_ingroup_id_list' ) ),
                    array( 'attribute', $inputField->contentClassAttribute->attribute( 'id' ) ),                           
                    array( 'attribute_identifier', $inputField->contentClassAttribute->attribute( 'identifier' ) ) );
                    
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
        $res->setKeys( $keyArray );        
        
        return $tpl->fetch( 'design:repository/contentclass_client/remote_class_search_form.tpl' );   
    }

    /**
     * @param string $action
     * @param array $parameters
     * @param bool $responseAsArray
     *
     * @return mixed
     * @throws Exception
     */
    protected function call( $action, $parameters, $responseAsArray )
    {
        $serverBaseUrl = $this->attributes['definition']['ServerBaseUrl'];        
        if ( !eZHTTPTool::getDataByURL( $serverBaseUrl, true ) )
        {
            throw new Exception( "Url $serverBaseUrl non raggiungibile" );
        }
        $query = $this->buildQueryString( $action, $parameters );
        eZDebug::writeNotice( $query, __METHOD__ . ' ' .  $action );
        return json_decode( eZHTTPTool::getDataByURL( $serverBaseUrl . '?' . $query ), $responseAsArray );
    }

    /**
     * @param string $action
     * @param array $parameters
     *
     * @return string
     */
    protected function buildQueryString( $action, $parameters )
    {
        $parameters = array(
            'action' => $action,
            'parameters' => $parameters
        );
        return http_build_query( $parameters );        
    }

    /**
     * @param array $fetchParameters
     *
     * @return mixed
     */
    public function fetchRemoteNavigationList( $fetchParameters )
    {        
        $result = $this->call( 'navigationList', $fetchParameters, true );
        return $result['response'];
    }

    /**
     * @param array $result
     *
     * @return array
     */
    protected function formatResult( $result )
    {
        $response = $result['response'];
        $requestParameters = array( 'action' => 'search' );
        foreach( $result['request']['parameters'] as $key => $value )
        {
            if ( !empty( $value ) )
            {
                $requestParameters[$key] = $value;
            }
        }        
        $prevUrl = false;
        $nextUrl = false;
        $limit = $response['fetch_parameters']['limit'];
        $offset = isset( $response['fetch_parameters']['offset'] ) ? $response['fetch_parameters']['offset'] : 0;
        if ( $response['count'] > ( $limit + $offset ) )
        {
            $requestParameters['offset'] = $limit + $offset;
            $nextUrl = $this->attributes['definition']['ClientBasePath'] . '?' . http_build_query( $requestParameters );
        }
        if ( $offset > 0 )
        {
            $requestParameters['offset'] = $offset - $limit;
            $prevUrl = $this->attributes['definition']['ClientBasePath'] . '?' . http_build_query( $requestParameters );
        }
        $results = $response;
        $results['prev'] = $prevUrl;
        $results['next'] = $nextUrl;
        return $results;
    }

    /**
     * @return array
     */
    protected function getResults()
    {
        if ( $this->results === null )
        {
            $this->results = array();            
            if ( $this->currentAction == 'search' )
            {
                $result = $this->call( 'search', $this->currentActionParameters, true );
                $this->results = $this->formatResult( $result );            
            }            
        }
        return $this->results;
    }

    /**
     * @return string
     */
    public function templateName()
    {
        return 'design:repository/contentclass_client/client.tpl';
    }

    /**
     * @throws Exception
     */
    protected function checkClass( $createClassIfNotExists = false )
    {        
        if ( class_exists( 'OCClassTools' ) )
        {
            try
            {                
                OCClassTools::setRemoteUrl( rtrim( $this->attributes['definition']['Url'], '/' ) . self::SERVER_CLASSDEFINITION_PATH );
                $tools = new OCClassTools( $this->classIdentifier, $createClassIfNotExists );                
                if ( $createClassIfNotExists )
                {
                    $tools->sync();
                }
                $tools->compare();
                $result = $tools->getData();            
                if ( $result->hasError )
                {
                    throw new Exception( var_export( $result->errors, 1 ) );
                }
            }
            catch( Exception $e )
            {
                throw new Exception( '[Repository classi di contenuto ' . OCClassTools::getRemoteUrl() . '] ' . $e->getMessage() );
            }
        }
        else
        {
            throw new Exception( "Libreria OCClassTools non trovata" );
        }
        
        $this->contentClass = eZContentClass::fetchByIdentifier( $this->classIdentifier );
        if ( !$this->contentClass instanceof eZContentClass )
        {
            throw new Exception( "La classe di contenuto non esiste in questa installazione" );
        }
    }

    /**
     * @param int $remoteNodeID
     * @param int $localParentNodeID
     *
     * @return eZContentObject
     * @throws Exception
     */
    public function importRemoteObjectFromRemoteNodeID( $remoteNodeID, $localParentNodeID )
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
    
    function import( $remoteReference, $localLocation )
    {
        $definition = $this->attribute( 'definition' );
        
        if ( !class_exists( 'OCOpenDataApiNode' ) )
        {
            throw new Exception( "Libreria OCOpenDataApiNode non trovata" );
        }
        
        $apiNodeUrl = rtrim( $definition['Url'], '/' ) . '/api/opendata/v1/content/node/' . $remoteReference;
        $remoteApiNode = OCOpenDataApiNode::fromLink( $apiNodeUrl );
        if ( !$remoteApiNode instanceof OCOpenDataApiNode )
        {
            throw new Exception( "Url remoto \"{$apiNodeUrl}\" non raggiungibile" );
        }
        $newObject = $remoteApiNode->createContentObject( $localLocation );
        if ( !$newObject instanceof eZContentObject )
        {
            throw new Exception( "Error importing object" );
        }
        return $newObject;
    }

}