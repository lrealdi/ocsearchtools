<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Reindicizza\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[class:]',
                                '',
                                array( 'class'  => 'Identificatore della classe' )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

try
{
    if ( isset( $options['class'] ) )
    {
        $classIdentifier = $options['class'];
    }
    else
    {
        throw new Exception( "Specificare la classe" );
    }
    
    $class = eZContentClass::fetchByIdentifier( $classIdentifier );
    if ( !$class instanceof eZContentClass )
    {
        throw new Exception( "Classe $classIdentifier non trovata" );
    }
    
    $objects = eZPersistentObject::fetchObjectList( eZContentObject::definition(),
                                                    array( 'id' ),
                                                    array( 'contentclass_id' => $class->attribute( 'id' ) ),
                                                    null,
                                                    null,
                                                    false );
    $ids = array();
    foreach( $objects as $object )
    {
        $ids[] = $object['id'];
    }
        
    if ( count( $ids ) > 0 )
    {
        $count = count( $ids );
        $cli->output( "Reindex $count objects" );
        $output = new ezcConsoleOutput();
        $progressBarOptions = array( 'emptyChar' => ' ', 'barChar'  => '=' );
        $progressBarOptions['minVerbosity'] = 10;    
        $progressBar = new ezcConsoleProgressbar( $output, intval( $count ), $progressBarOptions );
        $progressBar->start();
        
        foreach( $ids as $id )
        {            
            $progressBar->advance();
            eZDB::instance()->query( "INSERT INTO ezpending_actions( action, param ) VALUES ( 'index_object', '$id' )" );
        }
        $progressBar->finish();
    }
    
    
    $cli->output( "Starting processing pending search engine modifications" );

    $contentObjects = array();
    $db = eZDB::instance();
    
    $offset = 0;
    $limit = 50;
    
    //$searchEngine = eZSearch::getEngine();    
    //
    //if ( !$searchEngine instanceof ezpSearchEngine )
    //{
    //    $cli->error( "The configured search engine does not implement the ezpSearchEngine interface or can't be found." );
    //    $script->shutdown( 1 );
    //}
    
    $searchEngine = new eZSolr();
    
    $needRemoveWithUpdate = $searchEngine->needRemoveWithUpdate();
    
    while( true )
    {
        $entries = $db->arrayQuery(
            "SELECT param FROM ezpending_actions WHERE action = 'index_object' GROUP BY param ORDER BY min(created)",
            array( 'limit' => $limit, 'offset' => $offset )
        );
    
        if ( is_array( $entries ) && count( $entries ) != 0 )
        {
            foreach ( $entries as $entry )
            {
                $objectID = (int)$entry['param'];
    
                $cli->output( "\tIndexing object ID #$objectID" );
                $db->begin();
                $object = eZContentObject::fetch( $objectID );
                $removeFromPendingActions = true;
                if ( $object )
                {
                    if ( $needRemoveWithUpdate )
                    {
                        $searchEngine->removeObject( $object, false );
                    }
                    $removeFromPendingActions = $searchEngine->addObject( $object, false );
                }
    
                if ( $removeFromPendingActions )
                {
                    $db->query( "DELETE FROM ezpending_actions WHERE action = 'index_object' AND param = '$objectID'" );
                }
                else
                {
                    $cli->warning( "\tFailed indexing object ID #$objectID, keeping it in the queue." );
                    // Increase the offset to skip failing objects
                    ++$offset;
                }
    
                $db->commit();
            }
    
            $searchEngine->commit();
            // clear object cache to conserve memory
            eZContentObject::clearCache();
        }
        else
        {
            break; // No valid result from ezpending_actions
        }
    }
    
    $cli->output( "Done" );
    
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
