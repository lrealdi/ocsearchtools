<?php

$nodeID = false;
$node = eZContentObjectTreeNode::fetch( $Params['NodeID'] );
if ( $node instanceof eZContentObjectTreeNode )
{
    $nodeID = $Params['NodeID'];
}

if ( !$nodeID )
{
    echo 'Specificare un node ID valido';
}
else
{
    $alreadyPending = false;
    $pendings = eZPendingActions::fetchByAction( eZSolr::PENDING_ACTION_INDEX_SUBTREE );
    foreach( $pendings as $pending )
    {
        if ( $pending->attribute( 'param' ) == $nodeID )
        {
            $alreadyPending = true;
            break;
        }
    }
    if ( $alreadyPending )
    {
        echo "Il processo di indicizzazione per il sottoalbero di $nodeID è in coda";
    }
    else
    {
        $pendingAction = new eZPendingActions(
            array(
                'action' => eZSolr::PENDING_ACTION_INDEX_SUBTREE,
                'created' => time(),
                'param' => $nodeID
            )
        );
        $pendingAction->store();
        echo "Accodato il processo di indicizzazione per il sottoalbero di $nodeID";
    }    
} 

eZDisplayDebug();
eZExecution::cleanExit();

?>