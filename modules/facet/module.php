<?php

$Module = array( 'name' => 'Search' );

$ViewList = array();
$ViewList['proxy'] = array(
    'functions' => array( 'proxy' ),
    'script' => 'proxy.php',
    'params' => array( 'NodeID' ),
    'unordered_params' => array()
);

$FunctionList = array();
$FunctionList['proxy'] = array();
?>
