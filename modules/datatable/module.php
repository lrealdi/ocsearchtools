<?php


$Module = array( 'name' => 'OpenTeam Search',
                 'variable_params' => true );

$ViewList = array();

$ViewList['view'] = array( 'functions' => array( 'data' ),
                            'script' => 'view.php',
                            'params' => array( 'ParentNodes', 'Classes', 'Fields', 'DefaultFilters' ),
                            'unordered_params' => array() );

$FunctionList['data'] = array();



?>
