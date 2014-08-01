<?php

$Module = array( 'name' => 'Repository' );

$ViewList = array();
$ViewList['client'] = array(
    'functions' => array( 'client' ),
    'script' => 'client.php',
    'params' => array( 'RepositoryID' ),
    'unordered_params' => array()
);

$ViewList['server'] = array(
    'functions' => array( 'server' ),
    'script' => 'server.php',
    'params' => array( 'RepositoryID' ),
    'unordered_params' => array()
);

$FunctionList = array();
$FunctionList['client'] = array();
$FunctionList['server'] = array();
?>
