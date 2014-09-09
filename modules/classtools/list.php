<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$Result = array();
$Result['content'] = $tpl->fetch( 'design:classtools/list.tpl' );
$Result['path'] = array( array( 'text' => 'Classi' ,
                                'url' => false ) );
