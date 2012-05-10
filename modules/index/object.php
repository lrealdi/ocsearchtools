<?php

if ( NULL == $Params['ObjectID'] )
{
    echo 'Specificare un object ID';
}
else
{
    $ObjectID = $Params['ObjectID'];
    $searchEngine = new eZSolr();
    $object = eZContentObject::fetch( intval( $ObjectID ) );
    if ( $object )
    {
        echo "<h2>Indexing object ID: <em>" . $object->attribute( 'id' ) . "</em><br />Name: <em>" . $object->attribute( 'name' ) . "</em><br /> Main node ID:  <em>" . $object->attribute( 'main_node_id' ) . "</em><br />Class: <em>" . $object->attribute( 'class_identifier' ) . "</em></h2>";
        $attribues = $object->dataMap();
        echo '<table cellpadding="10">';
        echo '<tr>';
        echo '<th>Identifier</th>';
        echo '<th>Metadata</th>';
        echo '<th>ezfSolrDocumentFieldBase::getData</th>';
        echo '<tr>';
        foreach( $attribues as $i => $a )
        {
            if ( $a->attribute( 'contentclass_attribute' )->attribute( 'is_searchable' ) > 0 )
            {
                echo '<tr>';
                echo '<td>';
                echo $i . '<br /><small>(' . $a->attribute( 'data_type_string' ) . ')</small>';
                echo '</td><td>';
                var_dump( $a->metadata() );
                echo '</td><td>';
                $documentFieldBase = ezfSolrDocumentFieldBase::getInstance( $a );
                var_dump( $documentFieldBase->getData() ) ;
                echo '</td>';
                echo '</tr>';
            }
            else
            {
                echo '<tr>';
                echo '<td>';
                echo $i . '<br /><small>(' . $a->attribute( 'data_type_string' ) . ')</small>';
                echo '</td><td>';
                var_dump( $a->metadata() );
                echo '</td><td>';
                echo '<small><em>(not searchable)</em></small>';
                echo '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    
        $result = $searchEngine->addObject( $object, true );            
        echo '<h2>Index result: ' . var_export( $result, 1 ) . '</h2>';
    }
    else
    {
        echo 'Non esiste oggetto con ID #' . $ObjectID;
    }
}

eZDisplayDebug();
eZExecution::cleanExit();
?>