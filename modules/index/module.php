<?php

$Module = array( 'name' => 'Index' );

$ViewList = array();
$ViewList['object'] = array(
    'functions' => array( 'indexobject' ),
    'script' => 'object.php',
    'params' => array( 'ObjectID' ),
    'unordered_params' => array()
);

$ViewList['subtree'] = array(
    'functions' => array( 'indexobject' ),
    'script' => 'subtree.php',
    'params' => array( 'NodeID' ),
    'unordered_params' => array()
);

$FunctionList = array();
$FunctionList['indexobject'] = array();
?>
