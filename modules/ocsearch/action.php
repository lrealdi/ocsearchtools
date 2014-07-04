<?php

$module = $Params['Module'];
eZDebug::writeNotice( $_GET, __FILE__ );
OCClassSearchFormHelper::redirect( $_GET, $module );

?>