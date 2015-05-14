<?php

/** @var eZModule $module */
$Module = $Params['Module'];

$overrideParameters = array(
    'SearchSubTreeArray' => array( 298848 )
);
$query = OCCalendarSearchQuery::instance( $_GET, $overrideParameters );
$data = OCCalendarSearch::instance( $query );

$output = array(
    'query' => $_GET,
    'solrData' => $data->solrData(),    
    'facets' => $data->facets(),    
    'result' => $data->result()
);

header('Content-Type: application/json');
echo json_encode( $output );
//eZDisplayDebug();
eZExecution::cleanExit();