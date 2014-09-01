<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Reset ezfind id\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );


try
{
    $old = eZSolr::installationID();
    $db = eZDB::instance();    
    $db->arrayQuery( 'DELETE FROM ezsite_data WHERE name=\'ezfind_site_id\'' );
    $solr = new eZSolr();
    $solr::$InstallationID = null;
    $id = eZSolr::installationID();

    eZCLI::instance()->output( "Old: $old, New: $id" );
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
