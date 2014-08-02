<?php

interface OCRepositoryServerInterface
{
    /**
     * @return mixed
     */
    function run();

    /**
     * @return mixed
     */
    function info();
}