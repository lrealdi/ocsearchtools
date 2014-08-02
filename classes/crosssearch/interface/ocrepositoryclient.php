<?php

interface OCRepositoryClientInterface
{
    /**
     * @param $parameters
     *
     * @return mixed
     */
    function init( $parameters );

    /**
     * @return string
     */
    function templateName();

    /**
     * @param string $action
     */
    function setCurrentAction( $action );

    /**
     * @param $parameters
     */
    function setCurrentActionParameters( $parameters );
}