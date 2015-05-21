<?php

/** @var eZModule $module */
$Module = $Params['Module'];
$debug = isset( $_GET['_debug'] );

$contextIdentifier = $Params['ContextIdentifier'];
$contextParameters = $Params['UserParameters'];

try
{
    $searchContext = OCCalendarSearchContext::instance( $contextIdentifier, $contextParameters );
    $searchContext->setRequest( new OCCalendarSearchRequest( $_GET ) );
    if ( $debug ) $searchContext->enableDebug();    
    $output = $searchContext->getData();
}
catch ( Exception $e )
{
    $output = array( 'error' => $e->getMessage() );
}

if ( $debug )
{
    echo '<pre>';    
    //$output['solrData'] = $data->solrData();
    print_r($output);
    eZDisplayDebug();
}
else
{
    header('Content-Type: application/json');
    echo json_encode( $output );
}

eZExecution::cleanExit();