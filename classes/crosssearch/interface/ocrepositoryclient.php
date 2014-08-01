<?php

interface OCRepositoryClientInterface
{
    function init( $parameters );
    
    function templateName();
    
    function setCurrentAction( $action );
    
    function setCurrentActionParameters( $parameters );
}