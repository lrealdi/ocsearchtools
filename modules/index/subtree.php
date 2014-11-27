<?php

if ( NULL == $Params['NodeID'] )
{
    echo 'Specificare un node ID';
}
else
{
    $NodeID = $Params['NodeID'];
    $searchEngine = new eZSolr();
    $params = array( 'Limitation'  => array(),                     
                     'LoadDataMap' => false
                   );
    $length = 50;
    
    $count = eZContentObjectTreeNode::subTreeCountByNodeID( $params, $NodeID );
    echo "<h2>{$count} nodi</h2>"; flush();    
    echo '<ul>';
    $params = array_merge( $params, array( 'Offset' => 0 , 'Limit' => $length ) );
    do
    {
        $items = eZContentObjectTreeNode::subTreeByNodeID( $params, $NodeID );
        
        foreach ( $items as $item )
        {            
            $result = $searchEngine->addObject( $item->attribute( 'object' ), true );            
            echo '<li>Node ' . $item->attribute( 'node_id' ) . ' object ' . $item->attribute( 'contentobject_id' )
            . ' index result: ' . var_export( $result, 1 ) . '</li>';
            ob_flush();
            usleep(50000);
        }
        usleep(50000);
        $params['Offset'] += $length;
    } while ( count( $items ) == $length );
    echo '</ul>';
} 

eZDisplayDebug();
eZExecution::cleanExit();

?>