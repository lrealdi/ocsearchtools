<?php

/** @var eZModule $module */
$Module = $Params['Module'];
$debug = isset( $_GET['_debug'] );

$contextIdentifier = $Params['ContextIdentifier'];
$contextParameters = $Params['UserParameters'];

$query = OCCalendarSearchQuery::instance( $_GET, $contextIdentifier, $contextParameters );
$data = OCCalendarSearch::instance( $query );

$output = array(
    'result' => $data->result(),
    'facets' => $data->facets()    
);

if ( $debug )
{
    echo '<pre>';
    $output['query'] = $data->query();
    $output['solrData'] = $data->solrData();
    print_r($output);
    eZDisplayDebug();
}
else
{
    header('Content-Type: application/json');
    echo json_encode( $output );
}

eZExecution::cleanExit();