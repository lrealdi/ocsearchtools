<?php

if ( NULL == $Params['ObjectID'] )
{
    echo 'Specificare un object ID';
    eZExecution::cleanExit();
}
else
{
    $ObjectID = $Params['ObjectID'];
    $searchEngine = new eZSolr();
    $object = eZContentObject::fetch( intval( $ObjectID ) );
    if ( $object )
    {
        echo "<h2>Indicizzazione di <em>" . $object->attribute( 'name' ) . "</em> main_node:  " . $object->attribute( 'main_node_id' ) . " classe: " . $object->attribute( 'class_identifier' ) . "</h2>";
        $attribues = $object->dataMap();
        echo '<table>';
        foreach( $attribues as $i => $a )
        {
            echo '<tr>';
            echo '<td>';
            echo $i . '<br /><small>(' . $a->attribute( 'data_type_string' ) . ')</small>';
            echo '</td><td>';
            var_dump( $a->metadata() );
            echo '</td><td>';
            $documentFieldBase = ezfSolrDocumentFieldBase::getInstance( $a );
             echo '</td><td>';
            var_dump( $documentFieldBase->getData() ) ;
             echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    
        $result = $searchEngine->addObject( $object, true );            
        var_dump( $result );    
    }
    else
    {
        echo 'Non esiste oggetto con ID #' . $ObjectID;
    }
    eZExecution::cleanExit();
}
?>