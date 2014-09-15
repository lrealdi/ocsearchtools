<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Clean sqliimporttoken" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions(
    '[start][stop]',
    '',
    array(
        'start' => 'Start rsync status',
        'stop' => 'Stop rssync status'
    )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

$rsyncStatusName = 'rsync_status';
$statusRunning = 1;
$statusHalted = 0;

$rsyncStatus = eZSiteData::fetchByName( $rsyncStatusName );
if ( !$rsyncStatus )
{
    $row = array(
        'name'  => 'rsync_status',
        'value' => $statusHalted
    );
    $rsyncStatus = new eZSiteData( $row );
}

if ( $options['stop'] )
{
    $rsyncStatus->setAttribute( 'value', $statusHalted );
}
elseif ( $options['start'] )
{
    $rsyncStatus->setAttribute( 'value', $statusRunning );
}

$rsyncStatus->store();

$cli->output( "Rsync status: " . $rsyncStatus->attribute( 'value' ) );

$script->shutdown();