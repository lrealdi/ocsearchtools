<?php 

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$remote_id = $Params[ 'RemoteID' ];

if( $remote_id )
{
	$obj = eZContentObject::fetchByRemoteID( $remote_id );
	
	if( is_object( $obj ) )
	{
		$module->redirect( 'content', 'view', array( 'full',  $obj->attribute( 'main_node_id' ) ) );
	}
}

$Result[ 'content' ] = '<h1>Could not find given remote ID</h1><b>Remote ID:</b> "' . $remote_id. '"';

?>