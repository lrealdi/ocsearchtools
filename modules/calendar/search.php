<?php

/** @var eZModule $module */
$Module = $Params['Module'];
$debug = isset( $_GET['_debug'] );
$contextIdentifier = $Params['ContextIdentifier'];

$query = OCCalendarSearchQuery::instance( $_GET, $contextIdentifier );
$data = OCCalendarSearch::instance( $query );

$output = array(
    'facets' => $data->facets(),    
    'result' => $data->result()
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