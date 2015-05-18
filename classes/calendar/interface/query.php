<?php

interface OCCalendarSearchQueryInterface
{
    public function getRequest();

    public function getSolrData();

    public function makeFacets();

    public function makeDate();

    public function makeEvents();

    public function makeEventCount();
}