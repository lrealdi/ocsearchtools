<?php

$module = $Params['Module'];

if ( $Params['AjaxCall'] )
{
    //@todo
    eZExecution::cleanExit();
}
else
{
    OCClassSearchFormHelper::redirect( $_GET, $module );    
}


?>