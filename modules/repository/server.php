<?php

$http = eZHTTPTool::instance();
$module = $Params['Module'];
$repositoryID = $Params['RepositoryID'];
$data = array();
try
{
    $serverHandler = OCCrossSearch::serverHandler( $repositoryID );    
    if ( !$http->hasVariable( 'action' ) )
    {
        $data = $serverHandler->info();
    }
    else
    {
        $data = $serverHandler->run();
    }    
}
catch ( Exception $e )
{
    $data = array( 'error' => $e->getMessage() );    
}

header('Content-Type: application/json');
echo json_encode( $data );
//eZDisplayDebug();
eZExecution::cleanExit();