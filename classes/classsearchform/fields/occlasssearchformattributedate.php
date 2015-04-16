<?php

class OCClassSearchFormAttributeDate extends OCClassSearchFormAttributeBoundable
{
    /**
     * @return OCClassSearchFormFieldBoundsInterface
     */
    function getBoundsClassName()
    {
        return 'OCClassSearchFormDateFieldBounds';
    }
}