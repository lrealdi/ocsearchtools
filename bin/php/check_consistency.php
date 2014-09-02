<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Check consistency\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );


try
{
    
    $doUpdate = true;
 
    $cli = eZCLI::instance();
    $cli->setUseStyles( true );
    $script =& eZScript::instance();
    $script->initialize();
    $db = eZDB::instance();
    set_time_limit( 0 );
    $arrayResult1 = $db->arrayQuery( "SELECT id, contentclass_id, current_version FROM ezcontentobject" );
    $message = "First checking for content objects that has no contentobject_attributes at all...\n";
    $cli->output( $cli->stylize( 'cyan', $message ), false );
     
    $i = 0;
    foreach( $arrayResult1 as $item) {
            //check if object has no attributes of any version stored
            $hasAttribute = $db->arrayQuery( "SELECT contentobject_id FROM ezcontentobject_attribute WHERE contentobject_id = " . $item['id'] );
            
            if ( empty( $hasAttribute ) ) {
                    if ( $doUpdate ) {
                            $message = "Corrupt object, no attributes: " . $item['id'] . ". Deleting corrupt object with no attributes...\n";
                $cli->output( $cli->stylize( 'red', $message ), false );
                            $db->query( "DELETE FROM ezcontentobject WHERE ezcontentobject.id = " . $item['id'] );
                            $db->query( "DELETE FROM ezcontentobject_name WHERE ezcontentobject_name.contentobject_id = " . $item['id'] );
                            if ( $item['contentclass_id'] == 4 ) {
                                    $db->query( "DELETE FROM ezuser WHERE ezuser.contentobject_id = " . $item['id'] );
                            }
                    }
               else {
                $message = "Corrupt object, no attributes: " . $item['id'] . ", current_version:" . $item['current_version'] . "\n";        
            $cli->output( $cli->stylize( 'red', $message ), false );
               }
            $i++;
            }
    }
    $message = "Total corrupt objects with no attributes: " . $i . "\n";
    $cli->output( $cli->stylize( 'red', $message ), false );
     
    
    $arrayResult3 = $db->arrayQuery( "SELECT id, name FROM ezcontentobject" );
    
    $count = 0;
    $message = "Check nodes' consistency... ";
    $cli->output( $cli->stylize( 'cyan', $message ), false );
    foreach( $arrayResult3 as $item) {
    
            $hasNode = $db->arrayQuery( "SELECT contentobject_id FROM ezcontentobject_tree WHERE contentobject_id = " . $item['id'] );
        if ( empty( $hasNode ) ) {
            $count++;
            echo "The node does not exist for the object: " . $item['name'] . ", ID " . $item['id'] . "\n";
        }
    }
    $message = "Check completed. Affected objects:" . $count ."\n";
    $cli->output( $cli->stylize( 'red', $message ), false );
    
    
    if ( $doUpdate ) {
        foreach( $arrayResult3 as $item) {
            $hasNode = $db->arrayQuery( "SELECT contentobject_id FROM ezcontentobject_tree WHERE contentobject_id = " . $item['id'] );
        if ( empty( $hasNode ) ) {
            $message = "The node does not exist for the object: " . $item['name'] . ", ID " . $item['id'] . "\n";
            $cli->output( $cli->stylize( 'cyan', $message ), false );
                    $db->query( "DELETE FROM ezcontentobject WHERE id = " . $item['id'] );
            $message = "Object removed; ";
            $cli->output( $cli->stylize( 'cyan', $message ), false );
                    $db->query( "DELETE FROM ezcontentobject_name WHERE contentobject_id = " . $item['id'] );
            $message = "Object name removed; ";
            $cli->output( $cli->stylize( 'cyan', $message ), false );
                    $db->query( "DELETE FROM ezcontentobject_link WHERE (from_contentobject_id = " . $item['id'] . " OR to_contentobject_id = " . $item['id'] . ")" );
            $message = "Object links removed; ";
            $cli->output( $cli->stylize( 'cyan', $message ), false );
                    $db->query( "DELETE FROM ezcontentobject_version WHERE contentobject_id = " . $item['id'] );
            $message = "Object versions removed; ";
            $cli->output( $cli->stylize( 'cyan', $message ), false );
                    $db->query( "DELETE FROM ezcontentobject_attribute WHERE contentobject_id = " . $item['id'] );
            $message = "Object attributes removed" . "\n";
            $cli->output( $cli->stylize( 'cyan', $message ), false );
                    $db->query( "DELETE FROM eznode_assignment WHERE contentobject_id = " . $item['id'] );
            $message = "Nodes assignment removed" . "\n";
            $cli->output( $cli->stylize( 'cyan', $message ), false );
            }
         }
    }
    
    
    $arrayResult2 = $db->arrayQuery( "SELECT id, current_version, name FROM ezcontentobject" );
    $message = "Then checking for content objects that has contentobject_attributes, but not of the current_version...\n";
    $cli->output( $cli->stylize( 'cyan', $message ), false );
     
    $i = 0;
    foreach( $arrayResult2 as $item) {
            //check if current_version has content attributes
            $hasAttribute = $db->arrayQuery( "SELECT contentobject_id FROM ezcontentobject_attribute WHERE contentobject_id = " . $item['id'] . " AND version = " . $item['current_version'] );
     
            if ( empty( $hasAttribute ) ) {
                    if ( $doUpdate ) {
                            $previousCurrentVersion = $item['current_version'] - 1;
                            $message = "Corrupt object: " . $item['id'] . ", current_version: " . $item['current_version'] . ". Setting back to version: " . $previousCurrentVersion . "\n";
                $cli->output( $cli->stylize( 'cyan', $message ), false );
                            $db->query( "UPDATE ezcontentobject SET current_version = " . $previousCurrentVersion . " WHERE id = " . $item['id'] );
                    }
                    else {
                    $message = "Corrupt object: " . $item['id'] . ", current_version: " . $item['current_version'] . "\n";
                $cli->output( $cli->stylize( 'cyan', $message ), false );
                    }
            $i++;
            }
    }
    $message = "Total objects withp wrong current_version: " . $i . "\n";
    $cli->output( $cli->stylize( 'cyan', $message ), false );

    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
