<?php
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$nodeID = $Params['NodeID'];

$parameters = $_GET;
$redirectSuffix = '';

eZDebug::writeNotice( $_GET, __FILE__ );

if ( $http->hasGetVariable( 'UrlAlias' ) )
{
    $redirect = $http->getVariable( 'UrlAlias' );
}
else
{
    $node = eZContentObjectTreeNode::fetch( $nodeID );
    if ( $node instanceof eZContentObjectTreeNode )
    {
        $redirect = $node->attribute( 'url_alias' );
    }
}


$redirect = rtrim( $redirect, '/' );
foreach( $parameters as $key => $value )
{
    if ( $key != '' && $value != '' )
        $redirect .= "/({$key})/{$value}";
}
$module->redirectTo( $redirect . $redirectSuffix );

?>